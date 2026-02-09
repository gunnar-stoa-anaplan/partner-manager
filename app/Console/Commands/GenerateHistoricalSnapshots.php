<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\Resource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateHistoricalSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snapshots:generate {--weeks=12 : Number of weeks of historical data to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate historical snapshots from existing opportunities and resources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $weeks = (int) $this->option('weeks');

        $this->info("Generating {$weeks} weeks of historical snapshots...");

        // Get all opportunities and resources
        $opportunities = Opportunity::with('partner')->get();
        $resources = Resource::with('partner')->get();

        if ($opportunities->isEmpty() && $resources->isEmpty()) {
            $this->error('No opportunities or resources found. Please seed your database first.');
            return 1;
        }

        $this->info("Found {$opportunities->count()} opportunities and {$resources->count()} resources");

        // Create imports for each week
        $startDate = now()->subWeeks($weeks);
        $importsCreated = 0;
        $oppSnapshotsCreated = 0;
        $resSnapshotsCreated = 0;

        for ($i = 0; $i < $weeks; $i++) {
            $importDate = $startDate->copy()->addWeeks($i);

            // Create a fake import record
            $fileName = "weekly_snapshot_{$importDate->format('Y_m_d')}.csv";
            $import = Import::create([
                'user_id' => 1, // Default to first user
                'type' => 'opportunities',
                'file_name' => $fileName,
                'file_path' => "imports/historical/{$fileName}",
                'status' => 'completed',
                'total_rows' => $opportunities->count() + $resources->count(),
                'successful_rows' => $opportunities->count() + $resources->count(),
                'failed_rows' => 0,
                'partners_created' => 0,
                'started_at' => $importDate,
                'completed_at' => $importDate->copy()->addMinutes(5),
                'created_at' => $importDate,
                'updated_at' => $importDate,
            ]);

            $importsCreated++;

            // Create opportunity snapshots
            $oppSnapshots = [];
            foreach ($opportunities as $opportunity) {
                // Simulate some historical variation (80-100% of current value)
                $variation = rand(80, 100) / 100;

                $oppSnapshots[] = [
                    'opportunity_id' => $opportunity->id,
                    'import_id' => $import->id,
                    'snapshot_date' => $importDate->toDateString(),
                    'opportunity_name' => $opportunity->opportunity_name,
                    'account_name' => $opportunity->account_name,
                    'stage' => $opportunity->stage,
                    'amount' => $opportunity->amount ? $opportunity->amount * $variation : 0,
                    'close_date' => $opportunity->close_date,
                    'probability' => $opportunity->probability,
                    'owner' => $opportunity->owner,
                    'created_at' => $importDate,
                    'updated_at' => $importDate,
                ];
            }

            if (!empty($oppSnapshots)) {
                foreach (array_chunk($oppSnapshots, 500) as $chunk) {
                    DB::table('opportunity_snapshots')->insert($chunk);
                    $oppSnapshotsCreated += count($chunk);
                }
            }

            // Create resource snapshots (aggregated by partner/program/role)
            $resourceGroups = $resources->groupBy(function($resource) {
                return $resource->partner_id . '|' . $resource->program . '|' . $resource->role;
            });

            $resSnapshots = [];
            foreach ($resourceGroups as $key => $group) {
                [$partnerId, $program, $role] = explode('|', $key);

                // Simulate some historical variation (70-100% of current count)
                $variation = rand(70, 100) / 100;
                $totalCount = (int) (count($group) * $variation);

                // Simulate certification progress (0-80% completed in earlier weeks)
                $completionRate = min(0.8, ($i / $weeks) * 0.8);
                $completedCount = (int) ($totalCount * $completionRate);
                $startedCount = $totalCount - $completedCount;

                $resSnapshots[] = [
                    'partner_id' => $partnerId,
                    'import_id' => $import->id,
                    'snapshot_date' => $importDate->toDateString(),
                    'program' => $program,
                    'role' => $role ?: 'Unknown',
                    'started_count' => $startedCount,
                    'completed_count' => $completedCount,
                    'total_count' => $totalCount,
                    'unique_people_count' => $totalCount, // Simplified
                    'created_at' => $importDate,
                    'updated_at' => $importDate,
                ];
            }

            if (!empty($resSnapshots)) {
                foreach (array_chunk($resSnapshots, 500) as $chunk) {
                    DB::table('resource_snapshots')->insert($chunk);
                    $resSnapshotsCreated += count($chunk);
                }
            }

            $this->info("Week " . ($i + 1) . ": Created import #{$import->id} with snapshots");
        }

        $this->newLine();
        $this->info("✅ Historical snapshot generation complete!");
        $this->table(
            ['Type', 'Count'],
            [
                ['Imports Created', $importsCreated],
                ['Opportunity Snapshots', $oppSnapshotsCreated],
                ['Resource Snapshots', $resSnapshotsCreated],
            ]
        );

        $this->newLine();
        $this->info('You can now view your historical trends at /full-summary');

        return 0;
    }
}
