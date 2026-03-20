<?php

namespace Database\Seeders;

use App\Helpers\FiscalYearHelper;
use App\Models\Import;
use App\Models\Opportunity;
use App\Models\OpportunitySnapshot;
use App\Models\Partner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OpportunityHistorySeeder extends Seeder
{
    /**
     * Minimum number of columns expected in a valid opportunities file.
     * The malformed 2-2-2026 file has only 2 columns (pivot table export).
     */
    private const MIN_COLUMNS = 10;

    /**
     * Map Excel Sales Stage → probability percentage.
     */
    private const STAGE_PROBABILITY = [
        '1. Qualification and Discovery' => 20,
        '2. Solution Development' => 40,
        '3. Solution Validation' => 60,
        '4. Negotiate and Close' => 80,
        '5. Closed Won' => 100,
    ];

    /** @var array<string, string> */
    private array $lobToProductArea;

    /** @var list<string> */
    private array $validLobs;

    /** @var array<string, string> */
    private array $pfToLob;

    /** @var array<string, string> */
    private array $pnToLob;

    /** @var list<string> */
    private array $pfIgnore;

    public function run(): void
    {
        $this->loadLobMappings();
        $this->command->info('Seeding opportunity history from Excel snapshots...');

        Schema::withoutForeignKeyConstraints(function () {
            OpportunitySnapshot::truncate();
            Opportunity::truncate();
            Import::where('type', 'opportunities')->delete();
        });

        $folders = $this->discoverSnapshotFolders();

        if ($folders->isEmpty()) {
            $this->command->warn('No snapshot folders found in database/seeders/data/.');

            return;
        }

        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();
        $partnerMap = Partner::pluck('id', 'name');

        // Track opportunities by SFDC ID across snapshots for deduplication
        $knownOpportunities = []; // sfdc_id => db_id

        $totalOpportunities = 0;
        $totalSnapshots = 0;

        foreach ($folders as $folder) {
            $oppsPath = $folder['path'].'/opportunities.xlsx';

            if (! file_exists($oppsPath)) {
                $this->command->warn("  Skipping {$folder['name']} — no opportunities.xlsx found.");

                continue;
            }

            $snapshotDate = $folder['date'];

            $rows = $this->loadExcel($oppsPath);

            // Skip malformed files (e.g., pivot table exports with < 10 columns)
            if ($rows->isEmpty() || count(array_keys($rows->first())) < self::MIN_COLUMNS) {
                $this->command->warn("  Skipping {$folder['name']} — malformed opportunities file (".count(array_keys($rows->first() ?? [])).' columns).');

                continue;
            }

            $this->command->info("  Processing {$folder['name']} ({$rows->count()} raw rows)...");

            // Filter: must have a partner, exclude Anaplan PS
            $filteredRows = $rows->filter(function ($row) {
                $partnerHQ = trim($row['Partner Plan HQ'] ?? '');
                $finalImpl = trim($row['Final Implementation Partner'] ?? '');

                // Resolve partner: Partner Plan HQ takes priority
                $partner = $partnerHQ ?: $finalImpl;

                if (empty($partner)) {
                    return false;
                }

                // Exclude Anaplan PS
                if (str_contains(strtolower($partner), 'anaplan ps')) {
                    return false;
                }

                return true;
            });

            $this->command->info("    After filtering: {$filteredRows->count()} partner-related opportunities.");

            $import = Import::create([
                'type' => 'opportunities',
                'file_name' => "opportunities_{$folder['name']}.xlsx",
                'file_path' => "seeders/data/{$folder['name']}/opportunities.xlsx",
                'status' => 'completed',
                'user_id' => $admin->id,
                'started_at' => $snapshotDate,
                'completed_at' => $snapshotDate->copy()->addMinutes(5),
                'total_rows' => $rows->count(),
                'successful_rows' => 0,
                'failed_rows' => 0,
                'partners_created' => 0,
                'partners_updated' => 0,
                'opportunities_created' => 0,
                'opportunities_updated' => 0,
                'resources_created' => 0,
                'created_at' => $snapshotDate,
                'updated_at' => $snapshotDate,
            ]);

            $newOpps = 0;
            $updatedOpps = 0;
            $snapshotRecords = [];
            $skippedPartners = 0;

            foreach ($filteredRows as $row) {
                $partnerHQ = trim($row['Partner Plan HQ'] ?? '');
                $finalImpl = trim($row['Final Implementation Partner'] ?? '');
                $resolvedPartner = $partnerHQ ?: $finalImpl;

                // Match partner to DB — try Partner Plan HQ first, then Final Implementation Partner
                $partnerId = $partnerMap->get($resolvedPartner)
                    ?? $partnerMap->get($partnerHQ)
                    ?? $partnerMap->get($finalImpl);

                if (! $partnerId) {
                    $skippedPartners++;

                    continue;
                }

                // Extract SFDC ID from Link column
                $link = $row['Link'] ?? '';
                $sfdcId = $this->extractSfdcId($link);

                $closeDate = $this->parseDate($row['Close Date'] ?? null);
                $stage = trim($row['Sales Stage'] ?? '');
                $amount = $this->parseAmount($row['NNACV - SFDC'] ?? null);
                $lob = trim($row['LOB'] ?? '');
                $productFamily = trim($row['Product Family'] ?? '');
                $productName = trim($row['Product Name'] ?? '');
                $resolvedLob = $this->resolveLob($lob, $productFamily, $productName);
                $productArea = $this->lobToProductArea[$resolvedLob] ?? null;
                $probability = self::STAGE_PROBABILITY[$stage] ?? 50;
                $recordType = trim($row['Record Type (SFDC)'] ?? '');
                $partnerSalesRole = trim($row['Primary Partner Sales Role'] ?? '');

                // Determine revenue_type from Partner Sales Role
                $revenueType = strtolower($partnerSalesRole) === 'originated' ? 'sourced' : 'influenced';

                // Fiscal period
                $fiscal = FiscalYearHelper::calculateFiscalPeriod($closeDate);

                // Build excel_data JSON for fields not in the main schema
                $excelData = array_filter([
                    'parent_account' => trim($row['Parent Account'] ?? ''),
                    'geo' => trim($row['Geo'] ?? ''),
                    'region' => trim($row['Region'] ?? ''),
                    'partner_plan_hq' => $partnerHQ,
                    'final_implementation_partner' => $finalImpl,
                    'record_type' => $recordType,
                    'implementation_approach' => trim($row['Implementation Approach'] ?? ''),
                    'implementation_approach_details' => trim($row['Implementation Approach Details'] ?? ''),
                    'delivery_readiness_meeting_date' => $this->parseDate($row['Delivery Readiness Meeting Date'] ?? null)?->toDateString(),
                    'delivery_readiness_notes' => trim($row['Delivery Readiness Notes'] ?? ''),
                    'account_tier' => trim($row['Account Tier'] ?? ''),
                    'cs_health_score' => trim($row['CS Health Score'] ?? ''),
                    'delivery_assurance' => $this->parseBool($row['Delivery Assurance?'] ?? null),
                    'positioned_delivery_assurance' => $this->parseBool($row['Positioned Delivery Assurance?'] ?? null),
                    'app' => $this->parseBool($row['App?'] ?? null),
                    'app_detail' => trim($row['App Detail'] ?? ''),
                    'polaris' => $this->parseBool($row['Polaris'] ?? null),
                    'syrup' => $this->parseBool($row['Syrup'] ?? null),
                    'product_family' => trim($row['Product Family'] ?? ''),
                    'product_name' => trim($row['Product Name'] ?? ''),
                    'use_case_details' => trim($row['Use Case Details'] ?? ''),
                    'nnacv_md_adj' => $this->parseAmount($row['NNACV - MD Adj (SF)'] ?? null),
                    'stage_1_date' => $this->parseDate($row['Stage 1 Date'] ?? null)?->toDateString(),
                    'stage_2_date' => $this->parseDate($row['Stage 2 Date'] ?? null)?->toDateString(),
                    'stage_3_date' => $this->parseDate($row['Stage 3 Date'] ?? null)?->toDateString(),
                    'primary_partner_sales_role' => $partnerSalesRole,
                ], fn ($v) => $v !== '' && $v !== null);

                // Create or update the opportunity
                if (isset($knownOpportunities[$sfdcId])) {
                    $dbId = $knownOpportunities[$sfdcId];
                    Opportunity::where('id', $dbId)->update([
                        'stage' => $stage ?: null,
                        'amount' => $amount,
                        'close_date' => $closeDate,
                        'fiscal_quarter' => $fiscal['fiscal_quarter'],
                        'fiscal_year' => $fiscal['fiscal_year'],
                        'probability' => $probability,
                        'excel_data' => $excelData,
                        'import_id' => $import->id,
                        'updated_at' => $snapshotDate,
                    ]);
                    $updatedOpps++;
                } else {
                    $dbOpp = Opportunity::create([
                        'salesforce_id' => $sfdcId,
                        'opportunity_name' => trim($row['Opportunity Name'] ?? ''),
                        'account_name' => trim($row['Parent Account'] ?? ''),
                        'partner_id' => $partnerId,
                        'stage' => $stage ?: null,
                        'amount' => $amount,
                        'close_date' => $closeDate,
                        'fiscal_quarter' => $fiscal['fiscal_quarter'],
                        'fiscal_year' => $fiscal['fiscal_year'],
                        'type' => $recordType ?: null,
                        'lead_source' => $revenueType === 'sourced' ? 'Partner Referral' : null,
                        'revenue_type' => $revenueType,
                        'product_area' => $productArea,
                        'probability' => $probability,
                        'owner' => trim($row['Opportunity Owner'] ?? ''),
                        'excel_data' => $excelData,
                        'import_id' => $import->id,
                        'first_seen_at' => $snapshotDate,
                        'created_at' => $snapshotDate,
                        'updated_at' => $snapshotDate,
                    ]);
                    $knownOpportunities[$sfdcId] = $dbOpp->id;
                    $newOpps++;
                    $totalOpportunities++;
                }

                $dbId = $knownOpportunities[$sfdcId];

                // Create snapshot record
                $snapshotRecords[] = [
                    'opportunity_id' => $dbId,
                    'import_id' => $import->id,
                    'snapshot_date' => $snapshotDate->toDateString(),
                    'opportunity_name' => trim($row['Opportunity Name'] ?? ''),
                    'account_name' => trim($row['Parent Account'] ?? ''),
                    'partner_id' => $partnerId,
                    'stage' => $stage ?: null,
                    'amount' => $amount,
                    'close_date' => $closeDate?->toDateString(),
                    'fiscal_quarter' => $fiscal['fiscal_quarter'],
                    'fiscal_year' => $fiscal['fiscal_year'],
                    'type' => $recordType ?: null,
                    'lead_source' => $revenueType === 'sourced' ? 'Partner Referral' : null,
                    'revenue_type' => $revenueType,
                    'product_area' => $productArea,
                    'probability' => $probability,
                    'owner' => trim($row['Opportunity Owner'] ?? ''),
                    'created_at' => $snapshotDate,
                    'updated_at' => $snapshotDate,
                ];
            }

            // Batch insert snapshots
            if (! empty($snapshotRecords)) {
                foreach (array_chunk($snapshotRecords, 500) as $chunk) {
                    DB::table('opportunity_snapshots')->insert($chunk);
                }
                $totalSnapshots += count($snapshotRecords);
            }

            $import->update([
                'successful_rows' => $newOpps + $updatedOpps,
                'failed_rows' => $skippedPartners,
                'opportunities_created' => $newOpps,
                'opportunities_updated' => $updatedOpps,
            ]);

            $this->command->info("    Created {$newOpps} new, updated {$updatedOpps}, skipped {$skippedPartners} unmatched partners.");
            $this->command->info('    Created '.count($snapshotRecords).' snapshot records.');
        }

        $this->command->info("Done. Total: {$totalOpportunities} unique opportunities, {$totalSnapshots} snapshots.");
    }

    /**
     * Discover all date-based snapshot folders in database/seeders/data/.
     *
     * @return \Illuminate\Support\Collection<int, array{name: string, path: string, date: Carbon}>
     */
    private function discoverSnapshotFolders(): \Illuminate\Support\Collection
    {
        $dataDir = database_path('seeders/data');

        if (! is_dir($dataDir)) {
            return collect();
        }

        return collect(scandir($dataDir))
            ->filter(fn ($name) => preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $name))
            ->map(function ($name) use ($dataDir) {
                [$month, $day, $year] = explode('-', $name);

                return [
                    'name' => $name,
                    'path' => "{$dataDir}/{$name}",
                    'date' => Carbon::create((int) $year, (int) $month, (int) $day),
                ];
            })
            ->sortBy('date')
            ->values();
    }

    /**
     * Load an Excel file and return rows as a collection of associative arrays.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function loadExcel(string $path): \Illuminate\Support\Collection
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $headers = array_shift($rows);

        if (empty($headers)) {
            return collect();
        }

        return collect($rows)->map(function ($row) use ($headers) {
            $mapped = [];
            foreach ($headers as $col => $header) {
                $mapped[trim((string) $header)] = $row[$col];
            }

            return $mapped;
        });
    }

    /**
     * Extract Salesforce ID from a link URL or use the value directly.
     */
    private function extractSfdcId(?string $link): string
    {
        if (empty($link)) {
            return 'OPP-'.strtoupper(substr(md5((string) mt_rand()), 0, 12));
        }

        // Extract ID from URLs like https://anaplan.my.salesforce.com/006PO000008diJ7YAI
        if (str_contains($link, '/')) {
            $parts = explode('/', rtrim($link, '/'));

            return end($parts);
        }

        return $link;
    }

    /**
     * Parse an Excel date value (could be numeric serial, datetime string, or Carbon).
     */
    private function parseDate(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            // Excel serial date number
            return Carbon::createFromTimestamp(($value - 25569) * 86400);
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Parse an amount value from Excel (stored in $K, convert to dollars).
     */
    private function parseAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value * 1000;
    }

    /**
     * Load LOB mappings from the shared JSON config file.
     * This file is shared with partner-risk-analyzer for consistency.
     */
    private function loadLobMappings(): void
    {
        $path = database_path('seeders/data/lob-mappings.json');
        $config = json_decode(file_get_contents($path), true);

        $this->validLobs = $config['valid_lobs'];
        $this->lobToProductArea = $config['lob_to_product_area'];
        $this->pfToLob = $config['pf_to_lob'];
        $this->pnToLob = $config['pn_to_lob'];
        $this->pfIgnore = $config['pf_ignore'];
    }

    /**
     * Resolve LOB using 3-step inference (matches partner-risk-analyzer logic).
     *
     * Step 1: Accept known valid LOB values as-is.
     * Step 2: If LOB is missing/Other, infer from Product Family.
     * Step 3: If PF didn't help, infer from Product Name.
     */
    private function resolveLob(string $lob, string $productFamily, string $productName): string
    {
        // Step 1: Known valid LOB — accept as-is
        if ($lob !== '' && in_array($lob, $this->validLobs, true)) {
            return $lob;
        }

        // Step 2: Infer from Product Family (skip Platform/AI/Support parts)
        if ($productFamily !== '') {
            $parts = array_map('trim', explode(';', $productFamily));
            $ignore = array_merge($this->pfIgnore, ['']);
            $nonPlatform = array_filter($parts, fn (string $p) => ! in_array($p, $ignore, true));

            foreach ($nonPlatform as $part) {
                $upper = strtoupper($part);
                foreach ($this->pfToLob as $keyword => $mappedLob) {
                    if (str_contains($upper, strtoupper($keyword))) {
                        return $mappedLob;
                    }
                }
            }
        }

        // Step 3: Infer from Product Name
        if ($productName !== '') {
            $upper = strtoupper($productName);
            foreach ($this->pnToLob as $keyword => $mappedLob) {
                if (str_contains($upper, strtoupper($keyword))) {
                    return $mappedLob;
                }
            }
        }

        // Fallback: return original LOB (or 'Other' if blank)
        return $lob ?: 'Other';
    }

    /**
     * Parse a boolean value from Excel (could be TRUE/FALSE string or actual bool).
     */
    private function parseBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        return strtoupper(trim((string) $value)) === 'TRUE';
    }
}
