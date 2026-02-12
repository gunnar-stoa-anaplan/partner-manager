<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\FiscalYearHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ImportContactsRequest;
use App\Http\Requests\Api\V1\ImportOpportunitiesRequest;
use App\Http\Requests\Api\V1\ImportProjectsRequest;
use App\Http\Resources\V1\ImportResource;
use App\Models\Contact;
use App\Models\Import;
use App\Models\Opportunity;
use App\Models\OpportunitySnapshot;
use App\Models\Partner;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Services\ChangeDetectionService;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function opportunities(ImportOpportunitiesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $tokenId = $user->currentAccessToken()->id;

        $import = Import::create([
            'type' => 'opportunities',
            'source' => 'api',
            'file_name' => 'api_import_' . now()->format('Ymd_His') . '.json',
            'file_path' => '',
            'status' => 'processing',
            'user_id' => $user->id,
            'api_token_id' => $tokenId,
            'started_at' => now(),
        ]);

        $successfulRows = 0;
        $failedRows = 0;
        $partnersCreated = 0;
        $opportunitiesCreated = 0;
        $opportunitiesUpdated = 0;
        $errors = [];
        $partnerCache = [];
        $snapshotDate = $data['snapshot_date'];

        DB::beginTransaction();
        try {
            foreach ($data['opportunities'] as $index => $row) {
                try {
                    $partnerName = trim($row['partner_name']);

                    if (!isset($partnerCache[$partnerName])) {
                        $partner = Partner::firstOrCreate(
                            ['name' => $partnerName],
                            ['user_id' => $user->id]
                        );
                        if ($partner->wasRecentlyCreated) {
                            $partnersCreated++;
                        }
                        $partnerCache[$partnerName] = $partner->id;
                    }

                    $closeDate = !empty($row['close_date']) ? $row['close_date'] : null;
                    $fiscalPeriod = FiscalYearHelper::calculateFiscalPeriod($closeDate);

                    $opportunityData = [
                        'opportunity_name' => $row['opportunity_name'],
                        'account_name' => $row['account_name'] ?? null,
                        'partner_id' => $partnerCache[$partnerName],
                        'stage' => $row['stage'] ?? null,
                        'amount' => $row['amount'] ?? null,
                        'close_date' => $closeDate,
                        'fiscal_quarter' => $fiscalPeriod['fiscal_quarter'],
                        'fiscal_year' => $fiscalPeriod['fiscal_year'],
                        'type' => $row['type'] ?? null,
                        'revenue_type' => $row['revenue_type'] ?? null,
                        'product_area' => $row['product_area'] ?? null,
                        'probability' => $row['probability'] ?? null,
                        'owner' => $row['owner'] ?? null,
                        'lead_source' => $row['lead_source'] ?? null,
                        'import_id' => $import->id,
                    ];

                    $matchField = !empty($row['salesforce_id']) ? 'salesforce_id' : 'opportunity_name';
                    $matchValue = !empty($row['salesforce_id']) ? $row['salesforce_id'] : $row['opportunity_name'];

                    $opportunity = Opportunity::where($matchField, $matchValue)->first();

                    if ($opportunity) {
                        $opportunityData['previous_stage'] = $opportunity->stage;
                        $opportunityData['previous_close_date'] = $opportunity->close_date;
                        if (!$opportunity->first_seen_at) {
                            $opportunityData['first_seen_at'] = $opportunity->created_at;
                        }
                        $opportunity->update($opportunityData);
                        $opportunitiesUpdated++;
                    } else {
                        if (!empty($row['salesforce_id'])) {
                            $opportunityData['salesforce_id'] = $row['salesforce_id'];
                        }
                        $opportunityData['first_seen_at'] = now();
                        $opportunity = Opportunity::create($opportunityData);
                        $opportunitiesCreated++;
                    }

                    // Create snapshot
                    OpportunitySnapshot::create([
                        'opportunity_id' => $opportunity->id,
                        'import_id' => $import->id,
                        'snapshot_date' => $snapshotDate,
                        'opportunity_name' => $opportunity->opportunity_name,
                        'account_name' => $opportunity->account_name,
                        'partner_id' => $opportunity->partner_id,
                        'stage' => $opportunity->stage,
                        'amount' => $opportunity->amount,
                        'close_date' => $opportunity->close_date,
                        'fiscal_quarter' => $opportunity->fiscal_quarter,
                        'fiscal_year' => $opportunity->fiscal_year,
                        'type' => $opportunity->type,
                        'lead_source' => $opportunity->lead_source,
                        'revenue_type' => $opportunity->revenue_type,
                        'product_area' => $opportunity->product_area,
                        'probability' => $opportunity->probability,
                        'owner' => $opportunity->owner,
                    ]);

                    $successfulRows++;
                } catch (\Exception $e) {
                    $failedRows++;
                    if (count($errors) < 50) {
                        $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $import->update(['status' => 'failed', 'error_log' => $e->getMessage(), 'completed_at' => now()]);
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }

        // Detect and alert on changes (after commit)
        app(ChangeDetectionService::class)->detectChangesForImport($import);

        $import->update([
            'status' => 'completed',
            'total_rows' => count($data['opportunities']),
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'partners_created' => $partnersCreated,
            'opportunities_created' => $opportunitiesCreated,
            'opportunities_updated' => $opportunitiesUpdated,
            'error_log' => !empty($errors) ? implode("\n", $errors) : null,
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Import completed.',
            'data' => new ImportResource($import),
        ], 201);
    }

    public function projects(ImportProjectsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $tokenId = $user->currentAccessToken()->id;

        $partner = Partner::firstOrCreate(
            ['name' => trim($data['partner_name'])],
            ['user_id' => $user->id]
        );

        $import = Import::create([
            'type' => 'projects',
            'source' => 'api',
            'file_name' => 'api_import_' . now()->format('Ymd_His') . '.json',
            'file_path' => '',
            'status' => 'processing',
            'user_id' => $user->id,
            'api_token_id' => $tokenId,
            'started_at' => now(),
        ]);

        $successfulRows = 0;
        $failedRows = 0;
        $projectsCreated = 0;
        $projectsUpdated = 0;
        $errors = [];

        foreach ($data['projects'] as $index => $row) {
            try {
                $project = Project::where('partner_id', $partner->id)
                    ->where('name', $row['name'])
                    ->first();

                $projectData = [
                    'partner_id' => $partner->id,
                    'name' => $row['name'],
                    'status' => $row['status'] ?? 'planning',
                    'acv' => $row['acv'] ?? null,
                    'start_date' => $row['start_date'] ?? null,
                    'end_date' => $row['end_date'] ?? null,
                    'percent_complete' => $row['percent_complete'] ?? 0,
                    'client' => $row['client'] ?? null,
                    'delivery_team' => $row['delivery_team'] ?? null,
                    'initiative' => $row['initiative'] ?? null,
                    'budget' => $row['budget'] ?? null,
                ];

                if ($project) {
                    $project->update($projectData);
                    $projectsUpdated++;
                } else {
                    Project::create($projectData);
                    $projectsCreated++;
                }

                $successfulRows++;
            } catch (\Exception $e) {
                $failedRows++;
                if (count($errors) < 50) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }
        }

        $import->update([
            'status' => 'completed',
            'total_rows' => count($data['projects']),
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'partners_created' => $partner->wasRecentlyCreated ? 1 : 0,
            'projects_created' => $projectsCreated,
            'projects_updated' => $projectsUpdated,
            'error_log' => !empty($errors) ? implode("\n", $errors) : null,
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Import completed.',
            'data' => new ImportResource($import),
        ], 201);
    }

    public function contacts(ImportContactsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $tokenId = $user->currentAccessToken()->id;

        $partner = Partner::firstOrCreate(
            ['name' => trim($data['partner_name'])],
            ['user_id' => $user->id]
        );

        $import = Import::create([
            'type' => 'contacts',
            'source' => 'api',
            'file_name' => 'api_import_' . now()->format('Ymd_His') . '.json',
            'file_path' => '',
            'status' => 'processing',
            'user_id' => $user->id,
            'api_token_id' => $tokenId,
            'started_at' => now(),
        ]);

        $successfulRows = 0;
        $failedRows = 0;
        $contactsCreated = 0;
        $contactsUpdated = 0;
        $errors = [];

        foreach ($data['contacts'] as $index => $row) {
            try {
                $contact = Contact::where('partner_id', $partner->id)
                    ->where('email', $row['email'])
                    ->first();

                $contactData = [
                    'partner_id' => $partner->id,
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'title' => $row['title'] ?? null,
                    'phone' => $row['phone'] ?? null,
                ];

                if ($contact) {
                    $contact->update($contactData);
                    $contactsUpdated++;
                } else {
                    Contact::create($contactData);
                    $contactsCreated++;
                }

                $successfulRows++;
            } catch (\Exception $e) {
                $failedRows++;
                if (count($errors) < 50) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }
        }

        $import->update([
            'status' => 'completed',
            'total_rows' => count($data['contacts']),
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'partners_created' => $partner->wasRecentlyCreated ? 1 : 0,
            'contacts_created' => $contactsCreated,
            'contacts_updated' => $contactsUpdated,
            'error_log' => !empty($errors) ? implode("\n", $errors) : null,
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Import completed.',
            'data' => new ImportResource($import),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $import = Import::findOrFail($id);

        return response()->json([
            'data' => new ImportResource($import),
        ]);
    }
}
