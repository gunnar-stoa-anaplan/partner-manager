<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\Opportunity;
use App\Models\OpportunitySnapshot;
use App\Models\Partner;
use App\Models\Resource;
use App\Models\ResourceSnapshot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoricalDataSeeder extends Seeder
{
    private $stageProgression = [
        'Prospecting',
        'Qualification',
        'Needs Analysis',
        'Value Proposition',
        'Proposal/Price Quote',
        'Negotiation/Review',
        'Closed Won',
    ];

    private $programs = ['IFP', 'I&D', 'SPM', 'OWP'];

    private $roles = ['Delivery', 'Pre-Sales', 'Sales'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Historical Data Seeder...');

        // Clear existing data
        $this->command->info('Clearing existing data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OpportunitySnapshot::truncate();
        ResourceSnapshot::truncate();
        Opportunity::truncate();
        Resource::truncate();
        Import::truncate();
        DB::table('user_partner')->truncate();
        Partner::truncate();
        User::where('role', 'psm')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create base data
        $this->command->info('Creating base users and partners...');
        $admin = User::first() ?? User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Create Gunnar as a PSM user
        $gunnar = User::updateOrCreate(
            ['email' => 'gunnar.stoa@gmail.com'],
            [
                'name' => 'Gunnar Stoa',
                'password' => bcrypt('password'),
                'role' => 'psm',
            ]
        );

        // Create additional PSM users
        $psms = User::factory()->count(4)->create([
            'role' => 'psm',
        ]);

        // Add Gunnar to the PSM collection
        $psms->prepend($gunnar);

        // Create Accenture partner first
        $accenture = Partner::updateOrCreate(
            ['name' => 'Accenture'],
            [
                'website' => 'https://www.accenture.com',
                'industry' => 'Consulting',
                'user_id' => $gunnar->id,
            ]
        );

        // Assign Accenture to Gunnar
        if (! $accenture->psms()->where('user_id', $gunnar->id)->exists()) {
            $accenture->psms()->attach($gunnar->id, ['role' => 'psm']);
        }

        // Create additional partners
        $partners = Partner::factory()->count(19)->create();

        // Add Accenture to the beginning of partners collection
        $partners->prepend($accenture);

        // Assign PSMs to remaining partners
        foreach ($partners as $partner) {
            if ($partner->id === $accenture->id) {
                continue; // Skip Accenture, already assigned
            }

            $psm = $psms->random();
            $partner->update(['user_id' => $psm->id]);

            if (! $partner->psms()->where('user_id', $psm->id)->exists()) {
                $partner->psms()->attach($psm->id, ['role' => 'psm']);
            }
        }

        $this->command->info("Created {$partners->count()} partners and {$psms->count()} PSMs");
        $this->command->info('✓ Gunnar Stoa (gunnar.stoa@gmail.com) created with role=psm');
        $this->command->info('✓ Accenture partner assigned to Gunnar');

        // Track opportunities across weeks for evolution
        $trackedOpportunities = [];
        $trackedResources = []; // Track people and their certification progress

        // Create 40 weeks of historical data
        $weeksToSimulate = 40;
        $startDate = now()->subWeeks($weeksToSimulate);

        $this->command->info("Simulating {$weeksToSimulate} weeks of data...");
        $progressBar = $this->command->getOutput()->createProgressBar($weeksToSimulate);

        for ($week = 0; $week < $weeksToSimulate; $week++) {
            $weekDate = $startDate->copy()->addWeeks($week);

            // Create 3 imports per week (opportunities, resources, psm)
            $this->createWeeklyImports($admin, $weekDate, $partners, $trackedOpportunities, $trackedResources);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('✅ Historical data seeding completed!');
        $this->command->info('📊 Total Imports: '.Import::count());
        $this->command->info('📊 Total Opportunities: '.Opportunity::count());
        $this->command->info('📊 Total Opportunity Snapshots: '.OpportunitySnapshot::count());
        $this->command->info('📊 Total Resources: '.Resource::count());
        $this->command->info('📊 Total Resource Snapshots: '.ResourceSnapshot::count());
    }

    private function createWeeklyImports($admin, $weekDate, $partners, &$trackedOpportunities, &$trackedResources)
    {
        // 1. Create Opportunities Import
        $opportunitiesImport = Import::factory()
            ->opportunities()
            ->onDate($weekDate)
            ->create([
                'user_id' => $admin->id,
            ]);

        $this->createOpportunities($opportunitiesImport, $weekDate, $partners, $trackedOpportunities);

        // 2. Create Resources Import
        $resourcesImport = Import::factory()
            ->resources()
            ->onDate($weekDate->copy()->addHours(1))
            ->create([
                'user_id' => $admin->id,
            ]);

        $this->createResources($resourcesImport, $weekDate, $partners, $trackedResources);

        // 3. Occasionally create PSM Import
        if (rand(1, 4) === 1) { // 25% of weeks
            $psmImport = Import::factory()
                ->psm()
                ->onDate($weekDate->copy()->addHours(2))
                ->create([
                    'user_id' => $admin->id,
                ]);
        }
    }

    private function createOpportunities($import, $weekDate, $partners, &$trackedOpportunities)
    {
        $weekNumber = $weekDate->diffInWeeks(now()->subWeeks(40));

        // Add new opportunities early on, fewer later
        $newOpportunitiesCount = max(1, 10 - floor($weekNumber / 4));

        for ($i = 0; $i < $newOpportunitiesCount; $i++) {
            $partner = $partners->random();

            $opportunity = Opportunity::factory()
                ->forPartner($partner->id)
                ->forImport($import->id)
                ->stage($this->stageProgression[0]) // Start at Prospecting
                ->create();

            $trackedOpportunities[$opportunity->id] = [
                'current_stage_index' => 0,
                'weeks_in_stage' => 0,
                'partner_id' => $partner->id,
            ];
        }

        // Evolve existing opportunities
        foreach ($trackedOpportunities as $oppId => $tracking) {
            $opportunity = Opportunity::find($oppId);

            if (! $opportunity || $opportunity->stage === 'Closed Won' || $opportunity->stage === 'Closed Lost') {
                continue; // Skip closed opportunities
            }

            // Progress through stages (with some randomness)
            $tracking['weeks_in_stage']++;

            // Move to next stage every 3-5 weeks
            if ($tracking['weeks_in_stage'] >= rand(3, 5)) {
                $tracking['current_stage_index'] = min(
                    $tracking['current_stage_index'] + 1,
                    count($this->stageProgression) - 1
                );
                $tracking['weeks_in_stage'] = 0;

                $newStage = $this->stageProgression[$tracking['current_stage_index']];

                // Update the opportunity
                $opportunity->update([
                    'stage' => $newStage,
                    'probability' => $this->getProbabilityForStage($newStage),
                    'import_id' => $import->id,
                ]);
            } else {
                // Just update import_id to show it appeared in this import
                $opportunity->update(['import_id' => $import->id]);
            }

            $trackedOpportunities[$oppId] = $tracking;
        }

        // Create snapshots for all current opportunities
        $this->createOpportunitySnapshots($import, $weekDate);
    }

    private function createResources($import, $weekDate, $partners, &$trackedResources)
    {
        $weekNumber = $weekDate->diffInWeeks(now()->subWeeks(40));

        // Create resources with progression
        foreach ($partners->random(rand(10, 15)) as $partner) {
            $partnerKey = $partner->id;

            if (! isset($trackedResources[$partnerKey])) {
                $trackedResources[$partnerKey] = [];
            }

            // Add new people occasionally
            if (rand(1, 3) === 1) { // 33% chance
                $personName = fake()->name();
                $trackedResources[$partnerKey][$personName] = [
                    'certifications' => [],
                ];
            }

            // Progress existing people
            foreach ($trackedResources[$partnerKey] as $personName => $personData) {
                // Randomly work on certifications
                foreach ($this->programs as $program) {
                    foreach ($this->roles as $role) {
                        $certKey = "{$program}_{$role}";

                        if (! isset($personData['certifications'][$certKey])) {
                            // Start new certification 20% chance
                            if (rand(1, 5) === 1) {
                                $personData['certifications'][$certKey] = 'Started';

                                Resource::factory()
                                    ->forPartner($partner->id)
                                    ->forImport($import->id)
                                    ->started()
                                    ->program($program)
                                    ->create([
                                        'name' => $personName,
                                        'role' => $role,
                                    ]);
                            }
                        } elseif ($personData['certifications'][$certKey] === 'Started') {
                            // Complete certification 30% chance per week
                            if (rand(1, 10) <= 3) {
                                $personData['certifications'][$certKey] = 'Completed';

                                // Update existing resource or create completed one
                                Resource::factory()
                                    ->forPartner($partner->id)
                                    ->forImport($import->id)
                                    ->completed()
                                    ->program($program)
                                    ->create([
                                        'name' => $personName,
                                        'role' => $role,
                                    ]);
                            } else {
                                // Keep as started
                                Resource::factory()
                                    ->forPartner($partner->id)
                                    ->forImport($import->id)
                                    ->started()
                                    ->program($program)
                                    ->create([
                                        'name' => $personName,
                                        'role' => $role,
                                    ]);
                            }
                        } else {
                            // Already completed, just maintain the record
                            Resource::factory()
                                ->forPartner($partner->id)
                                ->forImport($import->id)
                                ->completed()
                                ->program($program)
                                ->create([
                                    'name' => $personName,
                                    'role' => $role,
                                ]);
                        }

                        $trackedResources[$partnerKey][$personName] = $personData;
                    }
                }
            }
        }

        // Create resource snapshots
        $this->createResourceSnapshots($import, $weekDate);
    }

    private function createOpportunitySnapshots($import, $snapshotDate)
    {
        $opportunities = Opportunity::where('import_id', $import->id)->get();
        $snapshots = [];
        $now = now();

        foreach ($opportunities as $opp) {
            $snapshots[] = [
                'opportunity_id' => $opp->id,
                'import_id' => $import->id,
                'snapshot_date' => $snapshotDate->toDateString(),
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
                'revenue_type' => $opp->revenue_type,
                'product_area' => $opp->product_area,
                'probability' => $opp->probability,
                'owner' => $opp->owner,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Batch insert
        if (! empty($snapshots)) {
            foreach (array_chunk($snapshots, 500) as $chunk) {
                DB::table('opportunity_snapshots')->insert($chunk);
            }
        }
    }

    private function createResourceSnapshots($import, $snapshotDate)
    {
        $aggregates = Resource::select(
            'partner_id',
            'program',
            'role',
            DB::raw('SUM(CASE WHEN status = "Started" THEN 1 ELSE 0 END) as started_count'),
            DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_count'),
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
                'snapshot_date' => $snapshotDate->toDateString(),
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

        // Batch insert
        if (! empty($snapshots)) {
            foreach (array_chunk($snapshots, 500) as $chunk) {
                DB::table('resource_snapshots')->insert($chunk);
            }
        }
    }

    private function getProbabilityForStage($stage): int
    {
        return match ($stage) {
            'Prospecting' => 10,
            'Qualification' => 20,
            'Needs Analysis' => 30,
            'Value Proposition' => 40,
            'Proposal/Price Quote' => 60,
            'Negotiation/Review' => 80,
            'Closed Won' => 100,
            'Closed Lost' => 0,
            default => 50,
        };
    }
}
