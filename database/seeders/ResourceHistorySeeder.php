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
        $partnerMap = Partner::pluck('id', 'name');

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
                $this->processExcelStreaming($resourcesPath, $partnerMap, $snapshotDate);

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
     * @return array{0: int, 1: array, 2: array, 3: int, 4: int}
     */
    private function processExcelStreaming(string $path, \Illuminate\Support\Collection $partnerMap, \Carbon\Carbon $snapshotDate): array
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

            $partnerId = $partnerMap->get($partnerName);

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
