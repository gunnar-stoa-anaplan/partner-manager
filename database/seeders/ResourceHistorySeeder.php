<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\Partner;
use App\Models\Resource;
use App\Models\ResourceSnapshot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ResourceHistorySeeder extends Seeder
{
    private const PROGRAMS = ['IFP', 'OWP', 'FCR', 'SPM', 'I&D', 'IBP', 'MFP'];

    /**
     * Priority aliases for multi-entity partners where the simplified Excel name
     * should route to a specific DB entity (typically the HQ) rather than the
     * first alphabetical fuzzy match.
     */
    private const PARTNER_ALIASES = [
        'Accenture' => 'Accenture - US',
        'Deloitte LLP' => 'Deloitte Consulting LLP - US (HQ)',
        'KPMG' => 'KPMG US (HQ)',
        'Genpact' => 'Genpact Intl',
        'Genpact International Inc. (Partner)' => 'Genpact Intl',
        'Fadmoor LLC-Partner' => 'Fadmoor LLC',
        'EyeOn Switzerland Sarl' => 'EyeOn',
        'Keyrus France' => 'Keyrus EMEA',
        'Keyrus Global' => 'Keyrus EPM Americas',
        'BearingPoint Holding B.V.' => 'Bearingpoint GmbH',
    ];

    public function run(): void
    {
        $this->command->info('Seeding resource history from Excel snapshots...');

        Schema::withoutForeignKeyConstraints(function () {
            ResourceSnapshot::truncate();
            Resource::truncate();
            Import::where('type', 'resources')->delete();
        });

        $folders = $this->discoverSnapshotFolders();

        if ($folders->isEmpty()) {
            $this->command->warn('No snapshot folders found in database/seeders/data/.');

            return;
        }

        $this->command->info("  Found {$folders->count()} snapshot folder(s): ".$folders->pluck('name')->implode(', '));

        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();
        $dbPartnerNames = Partner::pluck('id', 'name');

        // Seed the fuzzy cache with priority aliases for multi-entity partners
        $fuzzyCache = [];
        foreach (self::PARTNER_ALIASES as $alias => $dbName) {
            if (isset($dbPartnerNames[$dbName])) {
                $fuzzyCache[$alias] = $dbPartnerNames[$dbName];
            }
        }

        $totalResources = 0;
        $totalSnapshots = 0;

        foreach ($folders as $folder) {
            $resourcesPath = $folder['path'].'/resources.xlsx';

            if (! file_exists($resourcesPath)) {
                $this->command->warn("  Skipping {$folder['name']} — no resources.xlsx found.");

                continue;
            }

            $snapshotDate = $folder['date'];
            $this->command->info("  Processing {$folder['name']} (snapshot date: {$snapshotDate->toDateString()})...");

            // Stream rows to avoid loading entire 57K+ row file into memory
            [$rowCount, $resourceRecords, $snapshotAggregates, $matchedCount, $skippedCount] =
                $this->processExcelStreaming($resourcesPath, $dbPartnerNames, $fuzzyCache, $snapshotDate);

            $this->command->info("    Processed {$rowCount} rows from Excel.");

            $import = Import::create([
                'type' => 'resources',
                'file_name' => "resources_{$folder['name']}.xlsx",
                'file_path' => "seeders/data/{$folder['name']}/resources.xlsx",
                'status' => 'completed',
                'user_id' => $admin->id,
                'started_at' => $snapshotDate,
                'completed_at' => $snapshotDate->copy()->addMinutes(5),
                'total_rows' => $rowCount,
                'successful_rows' => $matchedCount,
                'failed_rows' => $skippedCount,
                'partners_created' => 0,
                'partners_updated' => 0,
                'opportunities_created' => 0,
                'opportunities_updated' => 0,
                'resources_created' => count($resourceRecords),
                'created_at' => $snapshotDate,
                'updated_at' => $snapshotDate,
            ]);

            // Batch insert resource records
            if (! empty($resourceRecords)) {
                // Set import_id on all records
                foreach ($resourceRecords as &$rec) {
                    $rec['import_id'] = $import->id;
                }
                unset($rec);

                foreach (array_chunk($resourceRecords, 500) as $chunk) {
                    DB::table('resources')->insert($chunk);
                }
                $totalResources += count($resourceRecords);
            }

            // Build and insert snapshot records
            $snapshotRecords = [];
            foreach ($snapshotAggregates as $agg) {
                $snapshotRecords[] = [
                    'partner_id' => $agg['partner_id'],
                    'import_id' => $import->id,
                    'snapshot_date' => $snapshotDate->toDateString(),
                    'program' => $agg['program'],
                    'role' => $agg['role'],
                    'started_count' => $agg['started'],
                    'completed_count' => $agg['completed'],
                    'total_count' => $agg['started'] + $agg['completed'],
                    'unique_people_count' => count(array_unique($agg['people'])),
                    'created_at' => $snapshotDate,
                    'updated_at' => $snapshotDate,
                ];
            }

            if (! empty($snapshotRecords)) {
                foreach (array_chunk($snapshotRecords, 500) as $chunk) {
                    DB::table('resource_snapshots')->insert($chunk);
                }
                $totalSnapshots += count($snapshotRecords);
            }

            $this->command->info("    Matched {$matchedCount} rows, skipped {$skippedCount} unmatched.");
            $this->command->info('    Created '.count($resourceRecords).' resource records, '.count($snapshotRecords).' snapshots.');

            // Free memory between files
            gc_collect_cycles();
        }

        $this->command->info("Done. Total: {$totalResources} resources, {$totalSnapshots} snapshots across {$folders->count()} imports.");
    }

    /**
     * Process an Excel file row-by-row using PhpSpreadsheet's row iterator
     * to avoid loading the entire file into memory at once.
     *
     * @param  array<string, int|null>  $fuzzyCache  Cached fuzzy lookups (passed by reference)
     * @return array{0: int, 1: array, 2: array, 3: int, 4: int}
     */
    private function processExcelStreaming(string $path, \Illuminate\Support\Collection $dbPartnerNames, array &$fuzzyCache, \Carbon\Carbon $snapshotDate): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [];
        $resourceRecords = [];
        $snapshotAggregates = [];
        $matchedCount = 0;
        $skippedCount = 0;
        $rowCount = 0;

        foreach ($sheet->getRowIterator() as $rowObj) {
            $cellIterator = $rowObj->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }

            // First row is headers
            if (empty($headers)) {
                $headers = array_map(fn ($h) => trim((string) ($h ?? '')), $rowData);

                continue;
            }

            $rowCount++;

            // Map row to associative array
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = $rowData[$i] ?? null;
            }

            $personName = trim($row['Unnamed: 0'] ?? $row[''] ?? 'Unknown');
            $partnerName = trim($row['Partner'] ?? '');
            $role = trim($row['Role'] ?? '');

            if (empty($partnerName) || empty($personName)) {
                continue;
            }

            // Resolve partner ID: exact match first, then fuzzy with cache
            $partnerId = $dbPartnerNames->get($partnerName);

            if ($partnerId === null) {
                if (array_key_exists($partnerName, $fuzzyCache)) {
                    $partnerId = $fuzzyCache[$partnerName];
                } else {
                    $partnerId = $this->findBestPartnerMatch($partnerName, $dbPartnerNames);
                    $fuzzyCache[$partnerName] = $partnerId;
                }
            }

            if (! $partnerId) {
                $skippedCount++;

                continue;
            }

            $matchedCount++;

            if ($role === 'Pre Sales') {
                $role = 'Pre-Sales';
            }

            foreach (self::PROGRAMS as $program) {
                $startedCol = "{$program} Started";
                $completeCol = "{$program} Complete";
                $startedVal = (int) ($row[$startedCol] ?? 0);
                $completeVal = (int) ($row[$completeCol] ?? 0);

                if ($startedVal <= 0 && $completeVal <= 0) {
                    continue;
                }

                $status = $completeVal > 0 ? 'Completed' : 'Started';

                $resourceRecords[] = [
                    'partner_id' => $partnerId,
                    'name' => $personName,
                    'role' => $role ?: null,
                    'program' => $program,
                    'status' => $status,
                    'date_certified' => $status === 'Completed' ? $snapshotDate->toDateString() : null,
                    'import_id' => null, // set after Import is created
                    'created_at' => $snapshotDate,
                    'updated_at' => $snapshotDate,
                ];

                $aggKey = "{$partnerId}|{$program}|{$role}";
                if (! isset($snapshotAggregates[$aggKey])) {
                    $snapshotAggregates[$aggKey] = [
                        'partner_id' => $partnerId,
                        'program' => $program,
                        'role' => $role ?: null,
                        'started' => 0,
                        'completed' => 0,
                        'people' => [],
                    ];
                }

                if ($status === 'Completed') {
                    $snapshotAggregates[$aggKey]['completed']++;
                } else {
                    $snapshotAggregates[$aggKey]['started']++;
                }
                $snapshotAggregates[$aggKey]['people'][] = $personName;
            }
        }

        // Release spreadsheet memory
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [$rowCount, $resourceRecords, $snapshotAggregates, $matchedCount, $skippedCount];
    }

    /**
     * Normalize a partner name for fuzzy comparison.
     * Ported from risk-analyzer.html's normalizePartnerName().
     */
    private function normalizePartnerName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/[,\.\(\)]/', '', $name); // Remove commas, periods, parentheses
        $name = preg_replace('/\s+-\s+.*/', '', $name); // Remove everything after " - " (regions)
        $name = preg_replace('/\b(ltd|llc|inc|gmbh|pty|pvt|sa|bv|ag|co|srl|llp|pte|plc|partner|former partner)\b/', '', $name);

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Find the best matching DB partner for a given Excel partner name.
     * Uses a multi-stage fuzzy matching algorithm ported from risk-analyzer.html.
     */
    private function findBestPartnerMatch(string $targetName, \Illuminate\Support\Collection $dbPartnerNames): ?int
    {
        $normalizedTarget = $this->normalizePartnerName($targetName);
        $dbNames = $dbPartnerNames->keys()->all();

        // 1. Case-insensitive exact match
        foreach ($dbNames as $dbName) {
            if (mb_strtolower($dbName) === mb_strtolower($targetName)) {
                return $dbPartnerNames[$dbName];
            }
        }

        // 2. Normalized exact match
        foreach ($dbNames as $dbName) {
            if ($this->normalizePartnerName($dbName) === $normalizedTarget) {
                return $dbPartnerNames[$dbName];
            }
        }

        // 3. Contains match (either direction, require minimum 4 chars)
        $minLength = 4;
        if (mb_strlen($normalizedTarget) >= $minLength) {
            foreach ($dbNames as $dbName) {
                $normalized = $this->normalizePartnerName($dbName);
                if (mb_strlen($normalized) < $minLength) {
                    continue;
                }
                if (str_contains($normalized, $normalizedTarget) || str_contains($normalizedTarget, $normalized)) {
                    return $dbPartnerNames[$dbName];
                }
            }
        }

        // 4. First significant word match (6+ chars to avoid false matches like "ey" → "keyrus")
        $targetFirstWord = explode(' ', $normalizedTarget)[0] ?? '';
        if (mb_strlen($targetFirstWord) > 5) {
            foreach ($dbNames as $dbName) {
                $normalized = $this->normalizePartnerName($dbName);
                $dbFirstWord = explode(' ', $normalized)[0] ?? '';
                if (mb_strlen($dbFirstWord) > 5 && $dbFirstWord === $targetFirstWord) {
                    return $dbPartnerNames[$dbName];
                }
            }
        }

        // 5. Token matching (at least 2 significant words match)
        $targetTokens = array_filter(explode(' ', $normalizedTarget), fn ($t) => mb_strlen($t) > 3);
        if (count($targetTokens) > 0) {
            foreach ($dbNames as $dbName) {
                $dbTokens = array_filter(explode(' ', $this->normalizePartnerName($dbName)), fn ($t) => mb_strlen($t) > 3);
                $matches = array_filter($targetTokens, function ($tt) use ($dbTokens) {
                    foreach ($dbTokens as $dt) {
                        if (str_contains($dt, $tt) || str_contains($tt, $dt)) {
                            return true;
                        }
                    }

                    return false;
                });
                if (count($matches) >= min(2, count($targetTokens))) {
                    return $dbPartnerNames[$dbName];
                }
            }
        }

        return null;
    }

    /**
     * Discover all date-based snapshot folders in database/seeders/data/.
     *
     * @return \Illuminate\Support\Collection<int, array{name: string, path: string, date: \Carbon\Carbon}>
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
                    'date' => \Carbon\Carbon::create((int) $year, (int) $month, (int) $day),
                ];
            })
            ->sortBy('date')
            ->values();
    }
}
