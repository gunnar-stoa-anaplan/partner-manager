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

class ResourceHistorySeeder extends Seeder
{
    private array $programs = ['IFP', 'I&D', 'SPM', 'OWP'];

    private array $roles = ['Delivery', 'Pre-Sales', 'Sales'];

    /**
     * Partner-specific profiles: [min_people, max_people, cert_speed_factor, max_certs_per_person]
     *
     * cert_speed_factor > 1 means faster completion (fewer weeks).
     * max_certs_per_person controls breadth of program coverage.
     */
    private function getPartnerProfile(string $name): array
    {
        return match (true) {
            str_contains($name, 'Accenture') => [22, 30, 1.3, 3],
            str_contains($name, 'Deloitte') => [18, 24, 1.2, 3],
            str_contains($name, 'PwC') => [14, 20, 1.1, 3],
            str_contains($name, 'KPMG') => [12, 18, 1.1, 2],
            str_contains($name, 'EY') => [12, 18, 1.0, 2],
            default => [5, 12, 1.0, 2],
        };
    }

    public function run(): void
    {
        $this->command->info('Seeding resource history (52 weeks)...');

        // Clear only resource-related data (non-destructive to partners/users/contacts/etc.)
        Schema::withoutForeignKeyConstraints(function () {
            ResourceSnapshot::truncate();
            Resource::truncate();
            Import::where('type', 'resources')->delete();
        });

        $partners = Partner::orderBy('id')->get();
        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();

        if ($partners->isEmpty()) {
            $this->command->warn('No partners found. Run PartnerSeeder first.');

            return;
        }

        $weeksToSimulate = 52;
        $startDate = now()->subWeeks($weeksToSimulate);

        // Pre-generate people and their certification journeys per partner
        $partnerPeople = [];

        foreach ($partners as $partner) {
            [$minPeople, $maxPeople, $speedFactor, $maxCerts] = $this->getPartnerProfile($partner->name);
            $peopleCount = rand($minPeople, $maxPeople);

            $people = [];
            for ($i = 0; $i < $peopleCount; $i++) {
                $role = $this->weightedRole();
                $certCount = rand(1, $maxCerts);
                $selectedPrograms = (array) array_rand(array_flip($this->programs), min($certCount, count($this->programs)));

                $certs = [];
                foreach ($selectedPrograms as $prog) {
                    // Stagger intro weeks: early hires start in first half, later hires throughout
                    $introWeek = $i < $peopleCount / 2
                        ? rand(0, (int) ($weeksToSimulate * 0.4))
                        : rand(0, $weeksToSimulate - 8);

                    // Weeks to complete varies by program difficulty and partner speed
                    $baseDuration = $this->programDifficulty($prog);
                    $weeksToComplete = max(4, (int) (rand($baseDuration - 3, $baseDuration + 5) / $speedFactor));

                    $certs[$prog] = [
                        'intro_week' => $introWeek,
                        'complete_week' => $introWeek + $weeksToComplete,
                    ];
                }

                $people[] = [
                    'name' => fake()->unique()->name(),
                    'role' => $role,
                    'certifications' => $certs,
                ];
            }

            $partnerPeople[$partner->id] = $people;
        }

        // Reset faker unique tracker
        fake()->unique(true);

        $totalSnapshots = 0;

        for ($week = 0; $week < $weeksToSimulate; $week++) {
            $weekDate = $startDate->copy()->addWeeks($week);

            // Create weekly import record — all timestamps match the file name date
            $import = Import::create([
                'type' => 'resources',
                'file_name' => 'weekly_resources_'.$weekDate->format('Y_m_d').'.xlsx',
                'file_path' => 'imports/weekly_resources_'.$weekDate->format('Y_m_d').'.xlsx',
                'status' => 'completed',
                'user_id' => $admin->id,
                'started_at' => $weekDate,
                'completed_at' => $weekDate->copy()->addMinutes(rand(2, 8)),
                'total_rows' => 0,
                'successful_rows' => 0,
                'failed_rows' => 0,
                'partners_created' => 0,
                'partners_updated' => 0,
                'opportunities_created' => 0,
                'opportunities_updated' => 0,
                'resources_created' => 0,
                'created_at' => $weekDate,
                'updated_at' => $weekDate,
            ]);

            // Compute snapshots per partner/program/role for this week
            $snapshots = [];
            $weekResourceCount = 0;

            foreach ($partnerPeople as $partnerId => $people) {
                // Aggregate by program+role
                $aggregates = [];

                foreach ($people as $person) {
                    foreach ($person['certifications'] as $program => $timing) {
                        if ($week < $timing['intro_week']) {
                            continue; // not yet started
                        }

                        $key = "{$program}|{$person['role']}";
                        if (! isset($aggregates[$key])) {
                            $aggregates[$key] = [
                                'program' => $program,
                                'role' => $person['role'],
                                'started' => 0,
                                'completed' => 0,
                                'names' => [],
                            ];
                        }

                        if ($week >= $timing['complete_week']) {
                            $aggregates[$key]['completed']++;
                        } else {
                            $aggregates[$key]['started']++;
                        }
                        $aggregates[$key]['names'][] = $person['name'];
                        $weekResourceCount++;
                    }
                }

                foreach ($aggregates as $agg) {
                    $snapshots[] = [
                        'partner_id' => $partnerId,
                        'import_id' => $import->id,
                        'snapshot_date' => $weekDate->toDateString(),
                        'program' => $agg['program'],
                        'role' => $agg['role'],
                        'started_count' => $agg['started'],
                        'completed_count' => $agg['completed'],
                        'total_count' => $agg['started'] + $agg['completed'],
                        'unique_people_count' => count(array_unique($agg['names'])),
                        'created_at' => $weekDate,
                        'updated_at' => $weekDate,
                    ];
                }
            }

            // Batch insert snapshots
            if (! empty($snapshots)) {
                foreach (array_chunk($snapshots, 500) as $chunk) {
                    DB::table('resource_snapshots')->insert($chunk);
                }
                $totalSnapshots += count($snapshots);
            }

            $import->update([
                'total_rows' => $weekResourceCount,
                'successful_rows' => $weekResourceCount,
                'resources_created' => $weekResourceCount,
            ]);
        }

        // Create current-state Resource records (latest state of each person's certifications)
        $resourceRecords = [];
        $latestImport = Import::where('type', 'resources')->latest('started_at')->first();

        foreach ($partnerPeople as $partnerId => $people) {
            foreach ($people as $person) {
                foreach ($person['certifications'] as $program => $timing) {
                    if ($weeksToSimulate < $timing['intro_week']) {
                        continue; // never started within simulation window
                    }

                    $isCompleted = $weeksToSimulate >= $timing['complete_week'];
                    $dateCertified = $isCompleted
                        ? $startDate->copy()->addWeeks($timing['complete_week'])->toDateString()
                        : null;

                    $resourceRecords[] = [
                        'partner_id' => $partnerId,
                        'name' => $person['name'],
                        'role' => $person['role'],
                        'program' => $program,
                        'status' => $isCompleted ? 'Completed' : 'Started',
                        'date_certified' => $dateCertified,
                        'import_id' => $latestImport->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Batch insert current resource records
        if (! empty($resourceRecords)) {
            foreach (array_chunk($resourceRecords, 500) as $chunk) {
                DB::table('resources')->insert($chunk);
            }
        }

        $totalPeople = collect($partnerPeople)->sum(fn ($people) => count($people));
        $completedCount = collect($resourceRecords)->where('status', 'Completed')->count();
        $startedCount = collect($resourceRecords)->where('status', 'Started')->count();

        $this->command->info("Seeded {$totalPeople} people with ".count($resourceRecords).' certifications across '.$partners->count().' partners.');
        $this->command->info("  Current state: {$completedCount} completed, {$startedCount} in progress");
        $this->command->info("  Created {$totalSnapshots} snapshot rows over {$weeksToSimulate} weekly imports.");

        // Show top partners
        foreach ($partners->take(5) as $partner) {
            $pResources = collect($resourceRecords)->where('partner_id', $partner->id);
            $pCompleted = $pResources->where('status', 'Completed')->count();
            $pTotal = $pResources->count();
            $pPeople = count($partnerPeople[$partner->id] ?? []);
            $this->command->info("  {$partner->name}: {$pPeople} people, {$pCompleted}/{$pTotal} certifications completed");
        }
        $this->command->info('  ... and '.max(0, $partners->count() - 5).' more partners');
    }

    /**
     * Weighted role distribution: more Delivery than Sales/Pre-Sales.
     */
    private function weightedRole(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 50) {
            return 'Delivery';
        }
        if ($rand <= 80) {
            return 'Pre-Sales';
        }

        return 'Sales';
    }

    /**
     * Base weeks to complete by program difficulty.
     */
    private function programDifficulty(string $program): int
    {
        return match ($program) {
            'IFP' => 14,   // Connected Planning — moderate
            'I&D' => 12,   // Import & Data — easier
            'SPM' => 16,   // Supply Planning — harder
            'OWP' => 10,   // Operational Workforce — easier
            default => 12,
        };
    }
}
