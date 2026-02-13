<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $this->call([
            // === Core Users & Entities ===
            GunnarSeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
            PartnerSeeder::class,
            ContactSeeder::class,
            ObjectiveSeeder::class,
            LeadSeeder::class,
            AccentureSeeder::class,

            // === Phase 1: Reference Data & Configuration ===
            PartnerPathSeeder::class,          // paths, stages, gates
            ScorecardWeightSeeder::class,      // scorecard dimension weights
            CertificationRequirementSeeder::class, // cert requirements per stage
            TaskTemplateSeeder::class,         // task templates per stage
            NotificationTemplateSeeder::class, // notification templates
            BlueprintSeeder::class,            // blueprints, outcomes, content items
            ProductSeeder::class,              // product catalog (needed before opportunities)

            // === Phase 2: Business Data ===
            OpportunityHistorySeeder::class,   // 52 weeks of opportunities + snapshots for analytics
            ProjectSeeder::class,              // projects from closed-won opportunities
            ResourceHistorySeeder::class,      // 52 weeks of resources + snapshots for analytics
            FundingSeeder::class,              // funding records
            PartnerEventSeeder::class,         // events with contact attendees
            EnablementSeeder::class,           // enablement events, sessions, registrations
            CertificationSeeder::class,        // certifications per contact
            ContactProductSeeder::class,       // product training per resource contact
            BlueprintAssignmentSeeder::class,  // assignments, enrollments, progress

            // === Phase 3: Plan Section ===
            PartnerPathEnrollmentSeeder::class, // enrollments + stage history
            PartnerTaskSeeder::class,           // tasks + comments (depends on enrollments)
            PartnerScorecardSeeder::class,      // scorecards (depends on weights)
            PartnerGoalSeeder::class,           // goals + dimensions
            ActionPlanSeeder::class,            // action plans + items

            // === Phase 4: Act Section ===
            PartnerSolutionSeeder::class,      // solutions + stage history
            SupportCaseSeeder::class,          // support cases
            PartnerAlertSeeder::class,         // alerts across all types
            QbrSessionSeeder::class,           // QBR sessions + 9 sections each
        ]);
    }
}
