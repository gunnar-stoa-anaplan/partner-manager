<?php

namespace App\Http\Controllers;

use App\Helpers\FiscalYearHelper;
use App\Helpers\PartnerNameHelper;
use App\Models\Import;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\Resource;
use App\Models\User;
use App\Services\ChangeDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index()
    {
        $imports = Import::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('configuration.imports.index', compact('imports'));
    }

    public function create()
    {
        return view('configuration.imports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            'type' => 'required|in:opportunities,accounts,partners,psm,resources',
        ]);

        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $filePath = $file->storeAs('imports', $fileName, 'local');

        // Create import record
        $import = Import::create([
            'type' => $request->type,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => 'pending',
            'user_id' => Auth::id() ?? 1, // Use authenticated user or default to user ID 1
            'started_at' => now(),
        ]);

        // Process the import
        try {
            $this->processImport($import);

            return redirect()
                ->route('imports.show', $import)
                ->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            $import->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return redirect()
                ->route('imports.index')
                ->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    public function show(Import $import)
    {
        $import->load('user', 'opportunities.partner');

        return view('configuration.imports.show', compact('import'));
    }

    public function destroy(Import $import)
    {
        // Delete associated file
        if (Storage::exists($import->file_path)) {
            Storage::delete($import->file_path);
        }

        $import->delete();

        return redirect()
            ->route('imports.index')
            ->with('success', 'Import deleted successfully!');
    }

    private function processImport(Import $import)
    {
        $import->update(['status' => 'processing']);

        $fullPath = storage_path('app/'.$import->file_path);

        if ($import->type === 'opportunities') {
            $this->processOpportunitiesImport($import, $fullPath);
        } elseif ($import->type === 'psm') {
            $this->processPSMImport($import, $fullPath);
        } elseif ($import->type === 'resources') {
            $this->processResourcesImport($import, $fullPath);
        }

        $import->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    private function processOpportunitiesImport(Import $import, string $filePath)
    {
        // Increase execution time for large imports
        set_time_limit(300); // 5 minutes

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            throw new \Exception('The Excel file is empty');
        }

        // Get headers from first row
        $headers = array_shift($rows);

        // Find column indices for Partner Plan HQ (E) and Final Implementation Partner (P)
        $partnerPlanHQIndex = 4; // Column E (0-indexed)
        $finalImplementationPartnerIndex = 15; // Column P (0-indexed)

        $totalRows = count($rows);
        $successfulRows = 0;
        $failedRows = 0;
        $partnersCreated = 0;
        $partnersUpdated = 0;
        $opportunitiesCreated = 0;
        $opportunitiesUpdated = 0;
        $errors = [];

        // Track unique partners
        $partnerNames = [];

        // First pass: Extract unique partner names
        foreach ($rows as $index => $row) {
            if (! empty($row[$partnerPlanHQIndex])) {
                $partnerNames[$row[$partnerPlanHQIndex]] = true;
            }
            if (! empty($row[$finalImplementationPartnerIndex])) {
                $partnerNames[$row[$finalImplementationPartnerIndex]] = true;
            }
        }

        // Create or update partners
        $partnerMap = [];
        foreach (array_keys($partnerNames) as $partnerName) {
            $partnerName = trim($partnerName);
            if (empty($partnerName)) {
                continue;
            }

            $partner = Partner::firstOrCreate(
                ['name' => $partnerName],
                [
                    'user_id' => Auth::id() ?? 1, // Use authenticated user or default to user ID 1
                    'website' => null,
                    'industry' => null,
                ]
            );

            if ($partner->wasRecentlyCreated) {
                $partnersCreated++;
            } else {
                $partnersUpdated++;
            }

            $partnerMap[$partnerName] = $partner->id;
        }

        // Second pass: Create opportunities
        foreach ($rows as $index => $row) {
            try {
                // Combine all row data with headers
                $rowData = array_combine($headers, $row);

                // Determine partner ID
                $partnerId = null;
                if (! empty($row[$finalImplementationPartnerIndex])) {
                    $partnerId = $partnerMap[trim($row[$finalImplementationPartnerIndex])] ?? null;
                } elseif (! empty($row[$partnerPlanHQIndex])) {
                    $partnerId = $partnerMap[trim($row[$partnerPlanHQIndex])] ?? null;
                }

                // Parse close date
                $closeDate = isset($rowData['Close Date']) ? $this->parseDate($rowData['Close Date']) : null;

                // Calculate fiscal quarter and year
                $fiscalPeriod = FiscalYearHelper::calculateFiscalPeriod($closeDate);

                // Create or update opportunity
                $opportunityData = [
                    'opportunity_name' => $rowData['Opportunity Name'] ?? 'Unnamed Opportunity',
                    'account_name' => $rowData['Account Name'] ?? null,
                    'partner_id' => $partnerId,
                    'stage' => $rowData['Stage'] ?? null,
                    'amount' => isset($rowData['Amount']) ? $this->parseAmount($rowData['Amount']) : null,
                    'close_date' => $closeDate,
                    'fiscal_quarter' => $fiscalPeriod['fiscal_quarter'],
                    'fiscal_year' => $fiscalPeriod['fiscal_year'],
                    'type' => $rowData['Type'] ?? null,
                    'description' => $rowData['Description'] ?? null,
                    'lead_source' => $rowData['Lead Source'] ?? null,
                    'probability' => isset($rowData['Probability']) ? $this->parsePercentage($rowData['Probability']) : null,
                    'owner' => $rowData['Owner'] ?? null,
                    'excel_data' => $rowData,
                    'import_id' => $import->id,
                ];

                // Try to match existing opportunity by name or salesforce_id
                $opportunity = Opportunity::where('opportunity_name', $opportunityData['opportunity_name'])
                    ->first();

                if ($opportunity) {
                    $opportunityData['previous_stage'] = $opportunity->stage;
                    $opportunityData['previous_close_date'] = $opportunity->close_date;
                    if (! $opportunity->first_seen_at) {
                        $opportunityData['first_seen_at'] = $opportunity->created_at;
                    }
                    $opportunity->update($opportunityData);
                    $opportunitiesUpdated++;
                } else {
                    $opportunityData['first_seen_at'] = now();
                    Opportunity::create($opportunityData);
                    $opportunitiesCreated++;
                }

                $successfulRows++;
            } catch (\Exception $e) {
                $failedRows++;
                $errors[] = 'Row '.($index + 2).': '.$e->getMessage();
            }
        }

        // Create historical snapshots for all opportunities from this import
        $this->createOpportunitySnapshots($import);

        // Detect and alert on changes
        app(ChangeDetectionService::class)->detectChangesForImport($import);

        // Update import statistics
        $import->update([
            'total_rows' => $totalRows,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'partners_created' => $partnersCreated,
            'partners_updated' => $partnersUpdated,
            'opportunities_created' => $opportunitiesCreated,
            'opportunities_updated' => $opportunitiesUpdated,
            'error_log' => ! empty($errors) ? implode("\n", $errors) : null,
            'summary' => [
                'total_unique_partners' => count($partnerNames),
                'partner_sources' => [
                    'partner_plan_hq' => count(array_filter($rows, fn ($r) => ! empty($r[$partnerPlanHQIndex]))),
                    'final_implementation_partner' => count(array_filter($rows, fn ($r) => ! empty($r[$finalImplementationPartnerIndex]))),
                ],
            ],
        ]);
    }

    private function parseAmount($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remove currency symbols and commas
        return (float) preg_replace('/[^0-9.]/', '', $value);
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parsePercentage($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remove % sign and convert to decimal
        return (float) str_replace('%', '', $value);
    }

    private function processPSMImport(Import $import, string $filePath)
    {
        // Increase execution time for large imports
        set_time_limit(300); // 5 minutes

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            throw new \Exception('The Excel file is empty');
        }

        // Get headers from first row
        $headers = array_shift($rows);

        $totalRows = count($rows);
        $successfulRows = 0;
        $failedRows = 0;
        $partnersUpdated = 0;
        $usersCreated = 0;
        $assignmentsCreated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                // Combine all row data with headers
                $rowData = array_combine($headers, $row);

                // Support both old format (Partner Plan HQ) and new format (Partner)
                $partnerName = $rowData['Partner'] ?? $rowData['Partner Plan HQ'] ?? null;
                $psmName = $rowData['PSM'] ?? null;
                $geography = $rowData['Geography'] ?? $rowData['Geo'] ?? null;
                $region = $rowData['Region'] ?? null;

                if (empty($partnerName)) {
                    $failedRows++;
                    $errors[] = 'Row '.($index + 2).': Missing partner name';

                    continue;
                }

                // Use fuzzy matching to find or create partner
                $partner = PartnerNameHelper::findOrCreate($partnerName, [
                    'geography' => $geography,
                    'region' => $region,
                ]);

                if (! $partner) {
                    $failedRows++;
                    $errors[] = 'Row '.($index + 2).': Could not create partner';

                    continue;
                }

                // Create or find PSM user if PSM name is provided
                $psmUserId = null;
                if (! empty($psmName)) {
                    // Check if user exists by name (case-insensitive)
                    $psmUser = User::whereRaw('LOWER(name) = ?', [strtolower($psmName)])->first();

                    if (! $psmUser) {
                        // Create new PSM user
                        $psmRoleId = \App\Models\Role::where('slug', 'psm')->where('type', 'internal')->value('id');
                        $psmUser = User::create([
                            'name' => $psmName,
                            'email' => Str::slug($psmName).'@placeholder.com', // Placeholder email
                            'password' => bcrypt(Str::random(16)), // Random password
                            'role_id' => $psmRoleId,
                        ]);
                        $usersCreated++;
                    } elseif ($psmUser->role?->slug !== 'psm') {
                        // Update existing user to PSM role if needed
                        $psmRoleId = \App\Models\Role::where('slug', 'psm')->where('type', 'internal')->value('id');
                        $psmUser->update(['role_id' => $psmRoleId]);
                    }

                    $psmUserId = $psmUser->id;

                    // Assign PSM to partner via user_partner pivot table (many-to-many)
                    $existingAssignment = DB::table('user_partner')
                        ->where('user_id', $psmUser->id)
                        ->where('partner_id', $partner->id)
                        ->where('role', 'psm')
                        ->exists();

                    if (! $existingAssignment) {
                        $partner->psms()->attach($psmUser->id, ['role' => 'psm']);
                        $assignmentsCreated++;
                    }
                }

                // Update partner with geography data and PSM assignment (matches GUI behavior)
                $partner->update([
                    'geography' => $geography,
                    'region' => $region,
                    'user_id' => $psmUserId, // Also set the simple user_id foreign key like GUI does
                ]);

                $partnersUpdated++;
                $successfulRows++;
            } catch (\Exception $e) {
                $failedRows++;
                $errors[] = 'Row '.($index + 2).': '.$e->getMessage();
            }
        }

        // Update import statistics
        $import->update([
            'total_rows' => $totalRows,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'partners_updated' => $partnersUpdated,
            'error_log' => ! empty($errors) ? implode("\n", $errors) : null,
            'summary' => [
                'users_created' => $usersCreated,
                'assignments_created' => $assignmentsCreated,
            ],
        ]);
    }

    private function processResourcesImport(Import $import, string $filePath)
    {
        // Increase execution time for large imports
        set_time_limit(300); // 5 minutes

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            throw new \Exception('The Excel file is empty');
        }

        // Get headers from first row
        $headers = array_shift($rows);

        $totalRows = count($rows);
        $successfulRows = 0;
        $failedRows = 0;
        $resourcesCreated = 0;
        $errors = [];

        // Load all partner names once for fuzzy matching
        $allPartnerNames = Partner::pluck('name', 'id')->toArray();
        $partnerIdsByName = [];
        foreach ($allPartnerNames as $id => $name) {
            $partnerIdsByName[$name] = $id;
        }

        // Load all existing resources to check for duplicates efficiently
        $existingResources = Resource::all()
            ->mapWithKeys(function ($resource) {
                $key = "{$resource->partner_id}|{$resource->name}|{$resource->program}|{$resource->status}";

                return [$key => true];
            })
            ->toArray();

        // Collect resources to batch insert
        $resourcesToInsert = [];
        $now = now();

        // Process each person row
        foreach ($rows as $index => $row) {
            try {
                // Combine all row data with headers
                $rowData = array_combine($headers, $row);

                // Get person info (first column might be '', 'Unnamed: 0', or have a name)
                $personName = $rowData[''] ?? $rowData['Unnamed: 0'] ?? $rowData['Name'] ?? 'Unknown';
                $partnerName = $rowData['Partner'] ?? null;
                $role = $rowData['Role'] ?? null;

                if (empty($partnerName)) {
                    $failedRows++;
                    $errors[] = 'Row '.($index + 2).': Missing partner name';

                    continue;
                }

                // Use fuzzy matching to find partner
                $matchedPartnerName = PartnerNameHelper::findBestMatch($partnerName, array_values($allPartnerNames));

                $partnerId = null;
                if ($matchedPartnerName) {
                    $partnerId = $partnerIdsByName[$matchedPartnerName] ?? null;
                }

                if (! $partnerId) {
                    $failedRows++;
                    $errors[] = 'Row '.($index + 2).": Partner '$partnerName' not found";

                    continue;
                }

                // Process certification columns (columns with ' Started' or ' Complete' suffix)
                $certificationCount = 0;
                foreach ($headers as $col) {
                    if ((str_contains($col, ' Started') || str_contains($col, ' Complete')) && ! empty($rowData[$col])) {
                        if ($rowData[$col] > 0) {
                            $program = str_replace([' Started', ' Complete'], '', $col);
                            $program = trim($program);
                            $status = str_contains($col, ' Started') ? 'Started' : 'Completed';

                            // Check if resource already exists using in-memory lookup
                            $resourceKey = "{$partnerId}|{$personName}|{$program}|{$status}";

                            if (! isset($existingResources[$resourceKey])) {
                                // Add to batch insert
                                $resourcesToInsert[] = [
                                    'partner_id' => $partnerId,
                                    'name' => $personName,
                                    'role' => $role,
                                    'program' => $program,
                                    'status' => $status,
                                    'import_id' => $import->id,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];

                                // Mark as existing to avoid duplicates within this import
                                $existingResources[$resourceKey] = true;
                                $resourcesCreated++;
                            }

                            $certificationCount++;
                        }
                    }
                }

                if ($certificationCount > 0) {
                    $successfulRows++;
                }
                // Note: Rows with no certifications are not counted as failures - they're just skipped
                // This is normal for resources files where some people may not have completed any certifications yet
            } catch (\Exception $e) {
                $failedRows++;
                // Only log first 100 errors to prevent error_log overflow
                if (count($errors) < 100) {
                    $errors[] = 'Row '.($index + 2).': '.$e->getMessage();
                } elseif (count($errors) === 100) {
                    $errors[] = '... and more errors (showing first 100 only)';
                }
            }
        }

        // Batch insert all new resources in chunks
        if (! empty($resourcesToInsert)) {
            foreach (array_chunk($resourcesToInsert, 500) as $chunk) {
                DB::table('resources')->insert($chunk);
            }
        }

        // Create historical snapshots for resource counts from this import
        $this->createResourceSnapshots($import);

        // Update import statistics
        $import->update([
            'total_rows' => $totalRows,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'resources_created' => $resourcesCreated,
            'error_log' => ! empty($errors) ? implode("\n", $errors) : null,
            'summary' => [
                'resources_created' => $resourcesCreated,
                'people_processed' => $successfulRows,
            ],
        ]);
    }

    /**
     * Create opportunity snapshots for historical tracking
     */
    private function createOpportunitySnapshots(Import $import)
    {
        $snapshotDate = $import->started_at->toDateString();
        $opportunities = Opportunity::where('import_id', $import->id)->get();

        $snapshots = [];
        $now = now();

        foreach ($opportunities as $opp) {
            $snapshots[] = [
                'opportunity_id' => $opp->id,
                'import_id' => $import->id,
                'snapshot_date' => $snapshotDate,
                'opportunity_name' => $opp->opportunity_name,
                'account_name' => $opp->account_name,
                'partner_id' => $opp->partner_id,
                'stage' => $opp->stage,
                'amount' => $opp->amount,
                'close_date' => $opp->close_date,
                'fiscal_quarter' => $opp->fiscal_quarter,
                'fiscal_year' => $opp->fiscal_year,
                'type' => $opp->type,
                'lead_source' => $opp->lead_source,
                'probability' => $opp->probability,
                'owner' => $opp->owner,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Batch insert snapshots
        if (! empty($snapshots)) {
            foreach (array_chunk($snapshots, 500) as $chunk) {
                DB::table('opportunity_snapshots')->insert($chunk);
            }
        }
    }

    /**
     * Create resource snapshots for historical tracking (aggregated by partner/program)
     */
    private function createResourceSnapshots(Import $import)
    {
        $snapshotDate = $import->started_at->toDateString();

        // Get aggregated resource counts per partner/program/role
        $aggregates = Resource::select(
            'partner_id',
            'program',
            'role',
            DB::raw('SUM(CASE WHEN status = \'Started\' THEN 1 ELSE 0 END) as started_count'),
            DB::raw('SUM(CASE WHEN status = \'Completed\' THEN 1 ELSE 0 END) as completed_count'),
            DB::raw('COUNT(*) as total_count'),
            DB::raw('COUNT(DISTINCT name) as unique_people_count')
        )
            ->whereNotNull('partner_id')
            ->groupBy('partner_id', 'program', 'role')
            ->get();

        $snapshots = [];
        $now = now();

        foreach ($aggregates as $agg) {
            $snapshots[] = [
                'partner_id' => $agg->partner_id,
                'import_id' => $import->id,
                'snapshot_date' => $snapshotDate,
                'program' => $agg->program,
                'role' => $agg->role,
                'started_count' => $agg->started_count,
                'completed_count' => $agg->completed_count,
                'total_count' => $agg->total_count,
                'unique_people_count' => $agg->unique_people_count,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Batch insert snapshots
        if (! empty($snapshots)) {
            foreach (array_chunk($snapshots, 500) as $chunk) {
                DB::table('resource_snapshots')->insert($chunk);
            }
        }
    }
}
