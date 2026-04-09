<?php

namespace Database\Seeders;

use App\Models\PartnerPath;
use App\Models\PartnerPathGate;
use App\Models\PartnerPathStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerPathSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereRelation('role', 'slug', 'admin')->first() ?? User::first();

        $path = PartnerPath::create([
            'name' => 'Implementation Partner',
            'description' => 'Standard progression path for implementation partners delivering Anaplan solutions.',
            'is_default' => true,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $stages = [
            ['stage_number' => 1, 'name' => 'Onboarded', 'description' => 'Partner registered, initial agreements signed, PSM assigned. No delivery capability yet.', 'color' => '#6C757D', 'icon' => 'fal fa-flag', 'sort_order' => 1],
            ['stage_number' => 2, 'name' => 'Enabled', 'description' => 'Core certifications obtained, first enablement completed, initial pipeline building.', 'color' => '#0D6EFD', 'icon' => 'fal fa-graduation-cap', 'sort_order' => 2],
            ['stage_number' => 3, 'name' => 'Performing', 'description' => 'Actively delivering projects, sourcing opportunities, meeting baseline metrics.', 'color' => '#198754', 'icon' => 'fal fa-chart-line', 'sort_order' => 3],
            ['stage_number' => 4, 'name' => 'Accelerating', 'description' => 'Consistently exceeding targets, expanding product coverage, high delivery quality.', 'color' => '#FFC107', 'icon' => 'fal fa-rocket', 'sort_order' => 4],
            ['stage_number' => 5, 'name' => 'Strategic', 'description' => 'Top-tier partner driving significant business, building solutions, leading in market.', 'color' => '#DC3545', 'icon' => 'fal fa-crown', 'sort_order' => 5],
        ];

        $stageModels = [];
        foreach ($stages as $stage) {
            $stageModels[$stage['stage_number']] = PartnerPathStage::create(array_merge($stage, [
                'partner_path_id' => $path->id,
            ]));
        }

        // Gate 1→2: Onboarded → Enabled
        $gate12 = [
            ['requirement_key' => 'certified_mb_count', 'requirement_label' => 'Model Builder Certifications', 'threshold_value' => 2, 'unit' => 'count', 'description' => 'At least 2 certified Model Builders'],
            ['requirement_key' => 'certified_sa_count', 'requirement_label' => 'Solution Architect Certifications', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'At least 1 certified Solution Architect'],
            ['requirement_key' => 'core_blueprints_completed', 'requirement_label' => 'Enablement Completion', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'At least 1 core blueprint fully completed'],
            ['requirement_key' => 'onboarding_tasks_pct', 'requirement_label' => 'PSM Onboarding Tasks', 'threshold_value' => 100, 'unit' => 'percentage', 'description' => 'All required onboarding tasks completed'],
            ['requirement_key' => 'salesforce_connected', 'requirement_label' => 'Salesforce Connected', 'threshold_value' => 1, 'unit' => 'boolean', 'description' => 'Partner Salesforce ID populated'],
            ['requirement_key' => 'contact_count', 'requirement_label' => 'Key Contacts Registered', 'threshold_value' => 3, 'unit' => 'count', 'description' => 'At least 3 contacts registered'],
        ];

        foreach ($gate12 as $i => $gate) {
            PartnerPathGate::create(array_merge($gate, [
                'partner_path_id' => $path->id,
                'from_stage_id' => $stageModels[1]->id,
                'to_stage_id' => $stageModels[2]->id,
                'comparison' => 'gte',
                'sort_order' => $i,
            ]));
        }

        // Gate 2→3: Enabled → Performing
        $gate23 = [
            ['requirement_key' => 'open_opportunity_count', 'requirement_label' => 'Active Pipeline', 'threshold_value' => 3, 'unit' => 'count', 'description' => 'At least 3 open opportunities'],
            ['requirement_key' => 'pipeline_value', 'requirement_label' => 'Pipeline Value', 'threshold_value' => 500000, 'unit' => 'currency', 'description' => 'Total open pipeline at least $500K'],
            ['requirement_key' => 'won_opp_count_12m', 'requirement_label' => 'Won Opportunities', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'At least 1 Closed-Won deal (trailing 12m)'],
            ['requirement_key' => 'certified_resource_count', 'requirement_label' => 'Certified Resources', 'threshold_value' => 4, 'unit' => 'count', 'description' => 'At least 4 active MB + SA certifications'],
            ['requirement_key' => 'app_blueprints_completed', 'requirement_label' => 'Blueprint Completion', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'At least 1 application-specific blueprint completed'],
            ['requirement_key' => 'completed_project_count', 'requirement_label' => 'Project Delivery', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'At least 1 completed project'],
            ['requirement_key' => 'qbr_count_12m', 'requirement_label' => 'QBR Cadence', 'threshold_value' => 2, 'unit' => 'count', 'description' => 'At least 2 QBRs completed (trailing 12m)'],
            ['requirement_key' => 'health_score', 'requirement_label' => 'Health Score', 'threshold_value' => 30, 'unit' => 'count', 'description' => 'Overall scorecard score at least 30'],
        ];

        foreach ($gate23 as $i => $gate) {
            PartnerPathGate::create(array_merge($gate, [
                'partner_path_id' => $path->id,
                'from_stage_id' => $stageModels[2]->id,
                'to_stage_id' => $stageModels[3]->id,
                'comparison' => 'gte',
                'sort_order' => $i,
            ]));
        }

        // Gate 3→4: Performing → Accelerating
        $gate34 = [
            ['requirement_key' => 'revenue_attainment_pct', 'requirement_label' => 'Revenue Achievement', 'threshold_value' => 80, 'unit' => 'percentage', 'description' => 'At least 80% of annual NNACV target'],
            ['requirement_key' => 'win_rate', 'requirement_label' => 'Win Rate', 'threshold_value' => 30, 'unit' => 'percentage', 'description' => 'Won / (Won + Lost) trailing 12m at least 30%'],
            ['requirement_key' => 'pipeline_coverage', 'requirement_label' => 'Pipeline Coverage', 'threshold_value' => 2.0, 'unit' => 'ratio', 'description' => 'Pipeline / Remaining target at least 2.0x'],
            ['requirement_key' => 'avg_csat', 'requirement_label' => 'Delivery Quality', 'threshold_value' => 4.0, 'unit' => 'count', 'description' => 'Average CSAT at least 4.0'],
            ['requirement_key' => 'ontime_delivery_pct', 'requirement_label' => 'On-Time Delivery', 'threshold_value' => 75, 'unit' => 'percentage', 'description' => 'At least 75% of projects delivered on-time'],
            ['requirement_key' => 'certified_resource_count', 'requirement_label' => 'Certified Resources', 'threshold_value' => 8, 'unit' => 'count', 'description' => 'At least 8 active MB + SA certifications'],
            ['requirement_key' => 'product_area_count', 'requirement_label' => 'Product Areas', 'threshold_value' => 2, 'unit' => 'count', 'description' => 'At least 2 distinct product areas with certifications'],
            ['requirement_key' => 'health_score', 'requirement_label' => 'Health Score', 'threshold_value' => 55, 'unit' => 'count', 'description' => 'Overall scorecard score at least 55'],
        ];

        foreach ($gate34 as $i => $gate) {
            PartnerPathGate::create(array_merge($gate, [
                'partner_path_id' => $path->id,
                'from_stage_id' => $stageModels[3]->id,
                'to_stage_id' => $stageModels[4]->id,
                'comparison' => 'gte',
                'sort_order' => $i,
            ]));
        }

        // Gate 4→5: Accelerating → Strategic
        $gate45 = [
            ['requirement_key' => 'revenue_attainment_pct', 'requirement_label' => 'Revenue Achievement', 'threshold_value' => 100, 'unit' => 'percentage', 'description' => 'At least 100% of annual NNACV target'],
            ['requirement_key' => 'win_rate', 'requirement_label' => 'Win Rate', 'threshold_value' => 40, 'unit' => 'percentage', 'description' => 'Won / (Won + Lost) trailing 12m at least 40%'],
            ['requirement_key' => 'pipeline_coverage', 'requirement_label' => 'Pipeline Coverage', 'threshold_value' => 3.0, 'unit' => 'ratio', 'description' => 'Pipeline / Remaining target at least 3.0x'],
            ['requirement_key' => 'avg_csat', 'requirement_label' => 'Delivery Quality', 'threshold_value' => 4.5, 'unit' => 'count', 'description' => 'Average CSAT at least 4.5'],
            ['requirement_key' => 'ontime_delivery_pct', 'requirement_label' => 'On-Time Delivery', 'threshold_value' => 85, 'unit' => 'percentage', 'description' => 'At least 85% of projects delivered on-time'],
            ['requirement_key' => 'certified_resource_count', 'requirement_label' => 'Certified Resources', 'threshold_value' => 15, 'unit' => 'count', 'description' => 'At least 15 active MB + SA certifications'],
            ['requirement_key' => 'product_area_count', 'requirement_label' => 'Product Areas', 'threshold_value' => 3, 'unit' => 'count', 'description' => 'At least 3 distinct product areas with certifications'],
            ['requirement_key' => 'solution_count', 'requirement_label' => 'Solution Assets', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'At least 1 registered solution/IP'],
            ['requirement_key' => 'self_sourced_pct', 'requirement_label' => 'Self-Sourced Ratio', 'threshold_value' => 40, 'unit' => 'percentage', 'description' => 'At least 40% of pipeline partner-originated'],
            ['requirement_key' => 'health_score', 'requirement_label' => 'Health Score', 'threshold_value' => 80, 'unit' => 'count', 'description' => 'Overall scorecard score at least 80'],
        ];

        foreach ($gate45 as $i => $gate) {
            PartnerPathGate::create(array_merge($gate, [
                'partner_path_id' => $path->id,
                'from_stage_id' => $stageModels[4]->id,
                'to_stage_id' => $stageModels[5]->id,
                'comparison' => 'gte',
                'sort_order' => $i,
            ]));
        }

        $this->seedApplicationBuilderPath($admin);
    }

    /**
     * Seed the Application Builder Partner path.
     *
     * This path is fundamentally different from Implementation Partner.
     * App Builder partners are effectively outsourced product teams that
     * build custom applications on the Anaplan platform, sell through
     * Anaplan field sales, and own the full lifecycle: design, validation,
     * GTM, field enablement, L1/L2 support, and ongoing enhancements.
     */
    private function seedApplicationBuilderPath(User $admin): void
    {
        $path = PartnerPath::create([
            'name' => 'Application Builder Partner',
            'description' => 'Progression path for partners building custom applications on the Anaplan platform. These partners follow Anaplan app design specifications, submit applications for validation, provide field sales enablement, pricing, contracts, demos, L1/L2 support, and ongoing delivery of enhancements and bug fixes.',
            'is_default' => false,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $stages = [
            [
                'stage_number' => 1,
                'name' => 'Onboarded',
                'description' => 'Partner agreement signed, app builder program enrolled, PSM assigned. Development sandbox provisioned, design specification documentation shared.',
                'color' => '#6C757D',
                'icon' => 'fal fa-file-signature',
                'sort_order' => 1,
            ],
            [
                'stage_number' => 2,
                'name' => 'Building',
                'description' => 'Application under active development. Partner following Anaplan design specs, building to platform standards. Regular design reviews with Anaplan engineering.',
                'color' => '#0D6EFD',
                'icon' => 'fal fa-code',
                'sort_order' => 2,
            ],
            [
                'stage_number' => 3,
                'name' => 'Validated',
                'description' => 'Application submitted and passed Anaplan technical validation. UX review complete, security audit passed, performance benchmarks met. Approved for marketplace listing.',
                'color' => '#198754',
                'icon' => 'fal fa-shield-check',
                'sort_order' => 3,
            ],
            [
                'stage_number' => 4,
                'name' => 'Go-to-Market',
                'description' => 'Application listed on marketplace. Sales materials, pricing, demo environments, and contracts finalized. Field sales and L1 support enablement delivered. Active selling motion underway.',
                'color' => '#FFC107',
                'icon' => 'fal fa-rocket',
                'sort_order' => 4,
            ],
            [
                'stage_number' => 5,
                'name' => 'Scaling',
                'description' => 'Proven field traction with multiple customers. L2 support and enhancement delivery operating smoothly. Revenue growing, expanding to new verticals or geos. Strategic co-sell relationship.',
                'color' => '#DC3545',
                'icon' => 'fal fa-chart-network',
                'sort_order' => 5,
            ],
        ];

        $stageModels = [];
        foreach ($stages as $stage) {
            $stageModels[$stage['stage_number']] = PartnerPathStage::create(array_merge($stage, [
                'partner_path_id' => $path->id,
            ]));
        }

        // ── Gate 1→2: Onboarded → Building ──────────────────────────────
        $gate12 = [
            ['requirement_key' => 'onboarding_tasks_pct', 'requirement_label' => 'Onboarding Tasks Complete', 'threshold_value' => 100, 'unit' => 'percentage', 'description' => 'All required onboarding tasks completed (agreement, NDA, program enrollment)'],
            ['requirement_key' => 'contact_count', 'requirement_label' => 'Key Contacts Registered', 'threshold_value' => 3, 'unit' => 'count', 'description' => 'Product owner, technical lead, and support contact registered'],
            ['requirement_key' => 'certified_mb_count', 'requirement_label' => 'Platform Certified Developers', 'threshold_value' => 2, 'unit' => 'count', 'description' => 'At least 2 developers with Model Builder certification'],
            ['requirement_key' => 'core_blueprints_completed', 'requirement_label' => 'Design Spec Review', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'App design specification blueprint reviewed and acknowledged'],
            ['requirement_key' => 'salesforce_connected', 'requirement_label' => 'CRM Connected', 'threshold_value' => 1, 'unit' => 'boolean', 'description' => 'Partner Salesforce ID populated for deal tracking'],
        ];

        foreach ($gate12 as $i => $gate) {
            PartnerPathGate::create(array_merge($gate, [
                'partner_path_id' => $path->id,
                'from_stage_id' => $stageModels[1]->id,
                'to_stage_id' => $stageModels[2]->id,
                'comparison' => 'gte',
                'sort_order' => $i,
            ]));
        }

        // ── Gate 2→3: Building → Validated ──────────────────────────────
        $gate23 = [
            ['requirement_key' => 'certified_resource_count', 'requirement_label' => 'Certified Developers', 'threshold_value' => 3, 'unit' => 'count', 'description' => 'At least 3 certified resources on the development team'],
            ['requirement_key' => 'app_blueprints_completed', 'requirement_label' => 'App Design Blueprint', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'Application-specific design blueprint fully completed'],
            ['requirement_key' => 'solution_count', 'requirement_label' => 'App Submitted for Validation', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'At least 1 application submitted for Anaplan technical validation'],
            ['requirement_key' => 'qbr_count_12m', 'requirement_label' => 'Design Review Cadence', 'threshold_value' => 2, 'unit' => 'count', 'description' => 'At least 2 design reviews completed with Anaplan engineering (trailing 12m)'],
            ['requirement_key' => 'completed_project_count', 'requirement_label' => 'Validation Milestones', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'UX review, security audit, and performance benchmarks passed'],
            ['requirement_key' => 'health_score', 'requirement_label' => 'Program Health Score', 'threshold_value' => 30, 'unit' => 'count', 'description' => 'Overall program health score at least 30'],
        ];

        foreach ($gate23 as $i => $gate) {
            PartnerPathGate::create(array_merge($gate, [
                'partner_path_id' => $path->id,
                'from_stage_id' => $stageModels[2]->id,
                'to_stage_id' => $stageModels[3]->id,
                'comparison' => 'gte',
                'sort_order' => $i,
            ]));
        }

        // ── Gate 3→4: Validated → Go-to-Market ─────────────────────────
        $gate34 = [
            ['requirement_key' => 'marketplace_approved', 'requirement_label' => 'Marketplace Listing Approved', 'threshold_value' => 1, 'unit' => 'boolean', 'description' => 'Application approved and listed on Anaplan marketplace'],
            ['requirement_key' => 'solution_count', 'requirement_label' => 'Sales Materials Package', 'threshold_value' => 1, 'unit' => 'count', 'description' => 'Complete sales kit: battle cards, ROI calculator, pricing sheet, demo script'],
            ['requirement_key' => 'certified_resource_count', 'requirement_label' => 'Support Team Certified', 'threshold_value' => 4, 'unit' => 'count', 'description' => 'At least 4 certified resources covering dev, support, and sales engineering'],
            ['requirement_key' => 'open_opportunity_count', 'requirement_label' => 'Initial Pipeline', 'threshold_value' => 2, 'unit' => 'count', 'description' => 'At least 2 qualified opportunities in pipeline'],
            ['requirement_key' => 'pipeline_value', 'requirement_label' => 'Pipeline Value', 'threshold_value' => 250000, 'unit' => 'currency', 'description' => 'At least $250K in qualified pipeline'],
            ['requirement_key' => 'contact_count', 'requirement_label' => 'Field Enablement Delivered', 'threshold_value' => 5, 'unit' => 'count', 'description' => 'At least 5 field contacts enabled (AEs, SEs trained on the application)'],
            ['requirement_key' => 'health_score', 'requirement_label' => 'Program Health Score', 'threshold_value' => 50, 'unit' => 'count', 'description' => 'Overall program health score at least 50'],
        ];

        foreach ($gate34 as $i => $gate) {
            PartnerPathGate::create(array_merge($gate, [
                'partner_path_id' => $path->id,
                'from_stage_id' => $stageModels[3]->id,
                'to_stage_id' => $stageModels[4]->id,
                'comparison' => 'gte',
                'sort_order' => $i,
            ]));
        }

        // ── Gate 4→5: Go-to-Market → Scaling ───────────────────────────
        $gate45 = [
            ['requirement_key' => 'won_opp_count_12m', 'requirement_label' => 'Closed-Won Customers', 'threshold_value' => 3, 'unit' => 'count', 'description' => 'At least 3 closed-won customer deals (trailing 12m)'],
            ['requirement_key' => 'revenue_attainment_pct', 'requirement_label' => 'Revenue Achievement', 'threshold_value' => 80, 'unit' => 'percentage', 'description' => 'At least 80% of annual app revenue target achieved'],
            ['requirement_key' => 'pipeline_value', 'requirement_label' => 'Active Pipeline', 'threshold_value' => 1000000, 'unit' => 'currency', 'description' => 'At least $1M in active pipeline'],
            ['requirement_key' => 'pipeline_coverage', 'requirement_label' => 'Pipeline Coverage', 'threshold_value' => 2.0, 'unit' => 'ratio', 'description' => 'Pipeline / Remaining target at least 2.0x'],
            ['requirement_key' => 'avg_csat', 'requirement_label' => 'Customer Satisfaction', 'threshold_value' => 4.0, 'unit' => 'count', 'description' => 'Average CSAT from deployed customers at least 4.0'],
            ['requirement_key' => 'certified_resource_count', 'requirement_label' => 'Full Support Team', 'threshold_value' => 8, 'unit' => 'count', 'description' => 'At least 8 certified resources across dev, L1 support, L2 support, and sales engineering'],
            ['requirement_key' => 'ontime_delivery_pct', 'requirement_label' => 'SLA Compliance', 'threshold_value' => 90, 'unit' => 'percentage', 'description' => 'At least 90% of L2 support tickets resolved within SLA'],
            ['requirement_key' => 'product_area_count', 'requirement_label' => 'Vertical/Geo Expansion', 'threshold_value' => 2, 'unit' => 'count', 'description' => 'Application deployed across at least 2 verticals or geographies'],
            ['requirement_key' => 'health_score', 'requirement_label' => 'Program Health Score', 'threshold_value' => 75, 'unit' => 'count', 'description' => 'Overall program health score at least 75'],
        ];

        foreach ($gate45 as $i => $gate) {
            PartnerPathGate::create(array_merge($gate, [
                'partner_path_id' => $path->id,
                'from_stage_id' => $stageModels[4]->id,
                'to_stage_id' => $stageModels[5]->id,
                'comparison' => 'gte',
                'sort_order' => $i,
            ]));
        }
    }
}
