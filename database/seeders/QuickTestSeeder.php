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

/**
 * Quick test seeder that creates 4 weeks of sample data for rapid testing.
 * Use this during development instead of HistoricalDataSeeder for faster iterations.
 */
class QuickTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Quick Test Seeder - Creating 4 weeks of sample data...');

        // Clear existing data
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

        // Create admin user
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

        // Create 1 additional PSM
        $psms = User::factory()->count(1)->create(['role' => 'psm']);
        $psms->prepend($gunnar);

        // Create Accenture partner
        $accenture = Partner::updateOrCreate(
            ['name' => 'Accenture'],
            [
                'website' => 'https://www.accenture.com',
                'industry' => 'Consulting',
                'user_id' => $gunnar->id,
            ]
        );

        // Assign Accenture to Gunnar
        if (!$accenture->psms()->where('user_id', $gunnar->id)->exists()) {
            $accenture->psms()->attach($gunnar->id, ['role' => 'psm']);
        }

        // Create 4 additional partners
        $partners = Partner::factory()->count(4)->create();
        $partners->prepend($accenture);

        // Assign PSMs to remaining partners
        foreach ($partners as $partner) {
            if ($partner->id === $accenture->id) {
                continue; // Skip Accenture, already assigned
            }

            $psm = $psms->random();
            $partner->update(['user_id' => $psm->id]);

            if (!$partner->psms()->where('user_id', $psm->id)->exists()) {
                $partner->psms()->attach($psm->id, ['role' => 'psm']);
            }
        }

        // Create 4 weeks of data
        for ($week = 0; $week < 4; $week++) {
            $weekDate = now()->subWeeks(4 - $week);

            // Opportunities Import
            $oppImport = Import::factory()
                ->opportunities()
                ->onDate($weekDate)
                ->create([
                    'user_id' => $admin->id,
                    'total_rows' => 10,
                    'successful_rows' => 10,
                    'opportunities_created' => 5,
                    'opportunities_updated' => 5,
                ]);

            // Create opportunities with progressing stages
            $stages = ['Prospecting', 'Qualification', 'Needs Analysis', 'Value Proposition'];
            foreach ($partners as $partner) {
                $stage = $stages[min($week, 3)];

                $opp = Opportunity::factory()
                    ->forPartner($partner->id)
                    ->forImport($oppImport->id)
                    ->stage($stage)
                    ->create();

                // Create snapshot
                OpportunitySnapshot::create([
                    'opportunity_id' => $opp->id,
                    'import_id' => $oppImport->id,
                    'snapshot_date' => $weekDate->toDateString(),
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
                ]);
            }

            // Resources Import
            $resImport = Import::factory()
                ->resources()
                ->onDate($weekDate->copy()->addHours(1))
                ->create([
                    'user_id' => $admin->id,
                    'total_rows' => 20,
                    'successful_rows' => 20,
                    'resources_created' => 10 + ($week * 2),
                ]);

            // Create resources with increasing completions
            foreach ($partners as $partner) {
                for ($i = 0; $i < 2; $i++) {
                    $personName = fake()->name();

                    // Started certification
                    Resource::factory()
                        ->forPartner($partner->id)
                        ->forImport($resImport->id)
                        ->started()
                        ->program('IFP')
                        ->create(['name' => $personName, 'role' => 'Delivery']);

                    // Completed certification (more each week)
                    if ($week >= 1) {
                        Resource::factory()
                            ->forPartner($partner->id)
                            ->forImport($resImport->id)
                            ->completed()
                            ->program('SPM')
                            ->create(['name' => $personName, 'role' => 'Pre-Sales']);
                    }
                }

                // Create resource snapshot
                ResourceSnapshot::create([
                    'partner_id' => $partner->id,
                    'import_id' => $resImport->id,
                    'snapshot_date' => $weekDate->toDateString(),
                    'program' => 'IFP',
                    'role' => 'Delivery',
                    'started_count' => 2,
                    'completed_count' => max(0, $week - 1),
                    'total_count' => 2 + max(0, $week - 1),
                    'unique_people_count' => 2,
                ]);
            }

            $this->command->info("Week {$week}: Created imports, opportunities, and resources");
        }

        $this->command->info('✅ Quick test data created!');
        $this->command->info("📊 Partners: {$partners->count()}");
        $this->command->info("📊 Imports: " . Import::count());
        $this->command->info("📊 Opportunities: " . Opportunity::count());
        $this->command->info("📊 Resources: " . Resource::count());
        $this->command->info("📊 Snapshots: " . (OpportunitySnapshot::count() + ResourceSnapshot::count()));
    }
}
