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
    }
}
