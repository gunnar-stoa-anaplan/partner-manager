<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\StrategyPlan;
use App\Models\StrategyPlanAssumption;
use App\Models\StrategyPlanBudget;
use App\Models\StrategyPlanInitiative;
use App\Models\StrategyPlanReview;
use App\Models\StrategyPlanTarget;
use App\Models\StrategyScenario;
use App\Models\StrategyScenarioOverride;
use App\Models\User;
use Illuminate\Database\Seeder;

class StrategySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Strategy Workbench data...');

        $coeRoleId = Role::where('slug', 'coe')->value('id');

        // === CoE Users (reuse existing CoE users from GunnarSeeder) ===
        $sarah = User::where('email', 'jennifer.blake@example.com')->first();
        $marcus = User::where('email', 'kevin.nakamura@example.com')->first();
        $emily = User::where('email', 'diana.costa@example.com')->first();
        $james = User::updateOrCreate(
            ['email' => 'james.park@example.com'],
            [
                'name' => 'James Park',
                'password' => bcrypt('password'),
                'role_id' => $coeRoleId,
            ]
        );

        $this->command->info('  ✓ Created 4 CoE users');

        // === FY27 Annual Plan (Active / Committed) ===
        $fy27 = StrategyPlan::updateOrCreate(
            ['name' => 'FY27 Partner Program Plan'],
            [
                'fiscal_year' => 'FY27',
                'period_type' => 'annual',
                'status' => 'active',
                'version' => 1,
                'description' => 'Comprehensive annual strategy for the Anaplan partner ecosystem. Focus on expanding Tier-1 partner capabilities, growing influenced pipeline, and driving enablement completions.',
                'created_by' => $sarah->id,
                'committed_at' => now()->subMonths(1),
                'committed_by' => $sarah->id,
            ]
        );

        // -- FY27 Targets --
        $fy27Targets = [
            [
                'category' => 'revenue',
                'metric_key' => 'sourced_revenue',
                'metric_label' => 'Partner Sourced Revenue',
                'unit' => 'currency',
                'target_value' => 12000000,
                'actual_value' => 3200000,
                'q1_target' => 2400000, 'q1_actual' => 3200000,
                'q2_target' => 3000000, 'q2_actual' => 1800000,
                'q3_target' => 3200000, 'q3_actual' => null,
                'q4_target' => 3400000, 'q4_actual' => null,
            ],
            [
                'category' => 'revenue',
                'metric_key' => 'influenced_revenue',
                'metric_label' => 'Partner Influenced Revenue',
                'unit' => 'currency',
                'target_value' => 28000000,
                'actual_value' => 7500000,
                'q1_target' => 5600000, 'q1_actual' => 7500000,
                'q2_target' => 7000000, 'q2_actual' => 4200000,
                'q3_target' => 7400000, 'q3_actual' => null,
                'q4_target' => 8000000, 'q4_actual' => null,
            ],
            [
                'category' => 'pipeline',
                'metric_key' => 'pipeline_value',
                'metric_label' => 'Partner Pipeline Value',
                'unit' => 'currency',
                'target_value' => 45000000,
                'actual_value' => 18500000,
                'q1_target' => 10000000, 'q1_actual' => 18500000,
                'q2_target' => 12000000, 'q2_actual' => 9800000,
                'q3_target' => 12000000, 'q3_actual' => null,
                'q4_target' => 11000000, 'q4_actual' => null,
            ],
            [
                'category' => 'growth',
                'metric_key' => 'tier1_partners',
                'metric_label' => 'Tier-1 Partner Count',
                'unit' => 'count',
                'target_value' => 8,
                'actual_value' => 5,
                'q1_target' => 5, 'q1_actual' => 5,
                'q2_target' => 6, 'q2_actual' => 5,
                'q3_target' => 7, 'q3_actual' => null,
                'q4_target' => 8, 'q4_actual' => null,
            ],
            [
                'category' => 'growth',
                'metric_key' => 'tier2_partners',
                'metric_label' => 'Tier-2 Partner Count',
                'unit' => 'count',
                'target_value' => 12,
                'actual_value' => 8,
                'q1_target' => 8, 'q1_actual' => 8,
                'q2_target' => 9, 'q2_actual' => 8,
                'q3_target' => 10, 'q3_actual' => null,
                'q4_target' => 12, 'q4_actual' => null,
            ],
            [
                'category' => 'enablement',
                'metric_key' => 'enablement_completions',
                'metric_label' => 'Enablement Completions',
                'unit' => 'count',
                'target_value' => 250,
                'actual_value' => 78,
                'q1_target' => 50, 'q1_actual' => 78,
                'q2_target' => 65, 'q2_actual' => 42,
                'q3_target' => 70, 'q3_actual' => null,
                'q4_target' => 65, 'q4_actual' => null,
            ],
            [
                'category' => 'certification',
                'metric_key' => 'active_certifications',
                'metric_label' => 'Active Certifications',
                'unit' => 'count',
                'target_value' => 120,
                'actual_value' => 45,
                'q1_target' => 35, 'q1_actual' => 45,
                'q2_target' => 30, 'q2_actual' => 22,
                'q3_target' => 30, 'q3_actual' => null,
                'q4_target' => 25, 'q4_actual' => null,
            ],
            [
                'category' => 'pipeline',
                'metric_key' => 'avg_win_rate',
                'metric_label' => 'Average Win Rate',
                'unit' => 'percentage',
                'target_value' => 35.0,
                'actual_value' => 32.5,
                'q1_target' => 30.0, 'q1_actual' => 32.5,
                'q2_target' => 33.0, 'q2_actual' => 28.0,
                'q3_target' => 36.0, 'q3_actual' => null,
                'q4_target' => 38.0, 'q4_actual' => null,
            ],
            [
                'category' => 'delivery',
                'metric_key' => 'projects_delivered',
                'metric_label' => 'Projects Delivered',
                'unit' => 'count',
                'target_value' => 60,
                'actual_value' => 18,
                'q1_target' => 12, 'q1_actual' => 18,
                'q2_target' => 15, 'q2_actual' => 10,
                'q3_target' => 16, 'q3_actual' => null,
                'q4_target' => 17, 'q4_actual' => null,
            ],
            [
                'category' => 'delivery',
                'metric_key' => 'avg_csat',
                'metric_label' => 'Average CSAT Score',
                'unit' => 'percentage',
                'target_value' => 85.0,
                'actual_value' => 82.3,
                'q1_target' => 82.0, 'q1_actual' => 82.3,
                'q2_target' => 84.0, 'q2_actual' => 79.5,
                'q3_target' => 86.0, 'q3_actual' => null,
                'q4_target' => 88.0, 'q4_actual' => null,
            ],
        ];

        foreach ($fy27Targets as $target) {
            StrategyPlanTarget::updateOrCreate(
                ['strategy_plan_id' => $fy27->id, 'metric_key' => $target['metric_key']],
                $target
            );
        }

        $this->command->info('  ✓ Created 10 FY27 targets with Q1 actuals');

        // -- FY27 Assumptions --
        $fy27Assumptions = [
            ['label' => 'Average Deal Conversion Rate', 'assumption_key' => 'conversion_rate', 'unit' => 'percentage', 'planned_value' => 0.2200, 'actual_value' => 0.1950],
            ['label' => 'Average Partner Ramp Time', 'assumption_key' => 'ramp_time', 'unit' => 'months', 'planned_value' => 6.0000, 'actual_value' => 7.5000],
            ['label' => 'Average Deal Size', 'assumption_key' => 'avg_deal_size', 'unit' => 'currency', 'planned_value' => 185000.0000, 'actual_value' => 172000.0000],
            ['label' => 'Annual Partner Churn Rate', 'assumption_key' => 'churn_rate', 'unit' => 'percentage', 'planned_value' => 0.0800, 'actual_value' => 0.0650],
            ['label' => 'MDF-to-Pipeline Ratio', 'assumption_key' => 'mdf_ratio', 'unit' => 'ratio', 'planned_value' => 5.0000, 'actual_value' => 4.2000],
            ['label' => 'Certification Completion Rate', 'assumption_key' => 'cert_completion_rate', 'unit' => 'percentage', 'planned_value' => 0.7500, 'actual_value' => 0.6800],
            ['label' => 'Average Sales Cycle Length', 'assumption_key' => 'sales_cycle_days', 'unit' => 'days', 'planned_value' => 90.0000, 'actual_value' => 105.0000],
            ['label' => 'Partner Attach Rate', 'assumption_key' => 'attach_rate', 'unit' => 'percentage', 'planned_value' => 0.4000, 'actual_value' => 0.3500],
        ];

        foreach ($fy27Assumptions as $assumption) {
            StrategyPlanAssumption::updateOrCreate(
                ['strategy_plan_id' => $fy27->id, 'assumption_key' => $assumption['assumption_key']],
                $assumption
            );
        }

        $this->command->info('  ✓ Created 8 FY27 assumptions');

        // -- FY27 Budgets --
        $fy27Budgets = [
            [
                'category' => 'mdf', 'label' => 'Market Development Funds',
                'planned_amount' => 2500000, 'actual_amount' => 680000,
                'q1_planned' => 500000, 'q1_actual' => 680000,
                'q2_planned' => 650000, 'q2_actual' => 320000,
                'q3_planned' => 700000, 'q3_actual' => null,
                'q4_planned' => 650000, 'q4_actual' => null,
            ],
            [
                'category' => 'enablement', 'label' => 'Enablement Programs',
                'planned_amount' => 800000, 'actual_amount' => 215000,
                'q1_planned' => 180000, 'q1_actual' => 215000,
                'q2_planned' => 200000, 'q2_actual' => 105000,
                'q3_planned' => 220000, 'q3_actual' => null,
                'q4_planned' => 200000, 'q4_actual' => null,
            ],
            [
                'category' => 'events', 'label' => 'Partner Events & Conferences',
                'planned_amount' => 600000, 'actual_amount' => 175000,
                'q1_planned' => 120000, 'q1_actual' => 175000,
                'q2_planned' => 180000, 'q2_actual' => 85000,
                'q3_planned' => 160000, 'q3_actual' => null,
                'q4_planned' => 140000, 'q4_actual' => null,
            ],
            [
                'category' => 'tooling', 'label' => 'Partner Tooling & Platform',
                'planned_amount' => 350000, 'actual_amount' => 92000,
                'q1_planned' => 90000, 'q1_actual' => 92000,
                'q2_planned' => 85000, 'q2_actual' => 45000,
                'q3_planned' => 90000, 'q3_actual' => null,
                'q4_planned' => 85000, 'q4_actual' => null,
            ],
            [
                'category' => 'headcount', 'label' => 'Partner Team Headcount',
                'planned_amount' => 1800000, 'actual_amount' => 450000,
                'q1_planned' => 450000, 'q1_actual' => 450000,
                'q2_planned' => 450000, 'q2_actual' => 450000,
                'q3_planned' => 450000, 'q3_actual' => null,
                'q4_planned' => 450000, 'q4_actual' => null,
            ],
            [
                'category' => 'marketing', 'label' => 'Co-Marketing Activities',
                'planned_amount' => 450000, 'actual_amount' => 128000,
                'q1_planned' => 100000, 'q1_actual' => 128000,
                'q2_planned' => 120000, 'q2_actual' => 65000,
                'q3_planned' => 130000, 'q3_actual' => null,
                'q4_planned' => 100000, 'q4_actual' => null,
            ],
        ];

        foreach ($fy27Budgets as $budget) {
            StrategyPlanBudget::updateOrCreate(
                ['strategy_plan_id' => $fy27->id, 'label' => $budget['label']],
                $budget
            );
        }

        $this->command->info('  ✓ Created 6 FY27 budget lines');

        // -- FY27 Initiatives --
        $fy27Initiatives = [
            [
                'category' => 'growth',
                'title' => 'Tier-1 Partner Acceleration Program',
                'description' => 'Intensive 90-day program to uplift 3 Tier-2 partners to Tier-1 through dedicated enablement, co-selling support, and executive sponsorship.',
                'status' => 'in_progress',
                'owner_id' => $marcus->id,
                'target_start_date' => now()->subMonths(2),
                'target_end_date' => now()->addMonths(1),
                'actual_start_date' => now()->subMonths(2),
            ],
            [
                'category' => 'enablement',
                'title' => 'Connected Planning Certification Bootcamp',
                'description' => 'Quarterly intensive certification bootcamp for partner consultants covering Anaplan model building, data integration, and UX design.',
                'status' => 'completed',
                'owner_id' => $emily->id,
                'target_start_date' => now()->subMonths(3),
                'target_end_date' => now()->subMonths(1),
                'actual_start_date' => now()->subMonths(3),
                'actual_end_date' => now()->subMonths(1),
            ],
            [
                'category' => 'go_to_market',
                'title' => 'Partner-Led Industry Solutions Launch',
                'description' => 'Develop and launch 4 industry-specific solution accelerators with strategic partners (Retail, Manufacturing, Banking, Healthcare).',
                'status' => 'in_progress',
                'owner_id' => $sarah->id,
                'target_start_date' => now()->subMonths(1),
                'target_end_date' => now()->addMonths(4),
                'actual_start_date' => now()->subMonths(1),
            ],
            [
                'category' => 'operational',
                'title' => 'Partner Portal UX Modernization',
                'description' => 'Redesign the partner portal experience with improved deal registration flow, self-service enablement access, and real-time performance dashboards.',
                'status' => 'planned',
                'owner_id' => $james->id,
                'target_start_date' => now()->addMonths(1),
                'target_end_date' => now()->addMonths(5),
            ],
            [
                'category' => 'investment',
                'title' => 'APAC Partner Ecosystem Expansion',
                'description' => 'Recruit and onboard 5 new partners in APAC region with focus on Japan, Australia, and Singapore markets. Includes dedicated MDF allocation and localized enablement.',
                'status' => 'planned',
                'owner_id' => $marcus->id,
                'target_start_date' => now()->addMonths(2),
                'target_end_date' => now()->addMonths(8),
            ],
        ];

        foreach ($fy27Initiatives as $initiative) {
            StrategyPlanInitiative::updateOrCreate(
                ['strategy_plan_id' => $fy27->id, 'title' => $initiative['title']],
                $initiative
            );
        }

        $this->command->info('  ✓ Created 5 FY27 initiatives');

        // === FY28 Draft Plan ===
        $fy28 = StrategyPlan::updateOrCreate(
            ['name' => 'FY28 Partner Program Plan'],
            [
                'fiscal_year' => 'FY28',
                'period_type' => 'annual',
                'status' => 'draft',
                'version' => 1,
                'description' => 'Next fiscal year partner program strategy with +15% revenue targets and expanded enablement investment.',
                'created_by' => $sarah->id,
            ]
        );

        // FY28 Targets (+15% from FY27)
        $fy28Targets = [
            ['category' => 'revenue', 'metric_key' => 'sourced_revenue', 'metric_label' => 'Partner Sourced Revenue', 'unit' => 'currency', 'target_value' => 13800000, 'q1_target' => 2760000, 'q2_target' => 3450000, 'q3_target' => 3680000, 'q4_target' => 3910000],
            ['category' => 'revenue', 'metric_key' => 'influenced_revenue', 'metric_label' => 'Partner Influenced Revenue', 'unit' => 'currency', 'target_value' => 32200000, 'q1_target' => 6440000, 'q2_target' => 8050000, 'q3_target' => 8510000, 'q4_target' => 9200000],
            ['category' => 'pipeline', 'metric_key' => 'pipeline_value', 'metric_label' => 'Partner Pipeline Value', 'unit' => 'currency', 'target_value' => 51750000, 'q1_target' => 11500000, 'q2_target' => 13800000, 'q3_target' => 13800000, 'q4_target' => 12650000],
            ['category' => 'growth', 'metric_key' => 'tier1_partners', 'metric_label' => 'Tier-1 Partner Count', 'unit' => 'count', 'target_value' => 10, 'q1_target' => 8, 'q2_target' => 9, 'q3_target' => 9, 'q4_target' => 10],
            ['category' => 'growth', 'metric_key' => 'tier2_partners', 'metric_label' => 'Tier-2 Partner Count', 'unit' => 'count', 'target_value' => 15, 'q1_target' => 12, 'q2_target' => 13, 'q3_target' => 14, 'q4_target' => 15],
            ['category' => 'enablement', 'metric_key' => 'enablement_completions', 'metric_label' => 'Enablement Completions', 'unit' => 'count', 'target_value' => 290, 'q1_target' => 60, 'q2_target' => 75, 'q3_target' => 80, 'q4_target' => 75],
            ['category' => 'certification', 'metric_key' => 'active_certifications', 'metric_label' => 'Active Certifications', 'unit' => 'count', 'target_value' => 140, 'q1_target' => 40, 'q2_target' => 35, 'q3_target' => 35, 'q4_target' => 30],
            ['category' => 'pipeline', 'metric_key' => 'avg_win_rate', 'metric_label' => 'Average Win Rate', 'unit' => 'percentage', 'target_value' => 38.0, 'q1_target' => 34.0, 'q2_target' => 36.0, 'q3_target' => 39.0, 'q4_target' => 41.0],
            ['category' => 'delivery', 'metric_key' => 'projects_delivered', 'metric_label' => 'Projects Delivered', 'unit' => 'count', 'target_value' => 70, 'q1_target' => 15, 'q2_target' => 17, 'q3_target' => 19, 'q4_target' => 19],
            ['category' => 'delivery', 'metric_key' => 'avg_csat', 'metric_label' => 'Average CSAT Score', 'unit' => 'percentage', 'target_value' => 88.0, 'q1_target' => 85.0, 'q2_target' => 87.0, 'q3_target' => 89.0, 'q4_target' => 90.0],
        ];

        foreach ($fy28Targets as $target) {
            StrategyPlanTarget::updateOrCreate(
                ['strategy_plan_id' => $fy28->id, 'metric_key' => $target['metric_key']],
                $target
            );
        }

        $this->command->info('  ✓ Created FY28 draft plan with 10 targets');

        // === Scenarios (based on FY27) ===

        // Scenario 1: Aggressive Growth
        $aggressive = StrategyScenario::updateOrCreate(
            ['name' => 'Aggressive Growth'],
            [
                'base_plan_id' => $fy27->id,
                'description' => 'Assumes accelerated partner recruitment and increased MDF investment to drive 20% higher pipeline targets. Requires additional headcount and event budget.',
                'status' => 'draft',
                'created_by' => $sarah->id,
            ]
        );

        // Apply overrides to Aggressive Growth scenario
        $aggressiveOverrides = [];
        $fy27TargetModels = StrategyPlanTarget::where('strategy_plan_id', $fy27->id)->get();
        foreach ($fy27TargetModels as $target) {
            if (in_array($target->metric_key, ['sourced_revenue', 'influenced_revenue', 'pipeline_value'])) {
                $aggressiveOverrides[] = [
                    'override_type' => 'target',
                    'reference_id' => $target->id,
                    'original_value' => $target->target_value,
                    'override_value' => round($target->target_value * 1.20, 2),
                ];
            }
        }

        $fy27BudgetModels = StrategyPlanBudget::where('strategy_plan_id', $fy27->id)->get();
        foreach ($fy27BudgetModels as $budget) {
            if ($budget->category === 'mdf') {
                $aggressiveOverrides[] = [
                    'override_type' => 'budget',
                    'reference_id' => $budget->id,
                    'original_value' => $budget->planned_amount,
                    'override_value' => round($budget->planned_amount * 1.30, 2),
                ];
            }
        }

        foreach ($aggressiveOverrides as $override) {
            StrategyScenarioOverride::updateOrCreate(
                ['strategy_scenario_id' => $aggressive->id, 'override_type' => $override['override_type'], 'reference_id' => $override['reference_id']],
                $override
            );
        }

        $this->command->info('  ✓ Created "Aggressive Growth" scenario with overrides');

        // Scenario 2: Conservative Approach
        $conservative = StrategyScenario::updateOrCreate(
            ['name' => 'Conservative Approach'],
            [
                'base_plan_id' => $fy27->id,
                'description' => 'Reduced targets reflecting economic headwinds and conservative hiring. Focuses on efficiency gains from existing partner base rather than expansion.',
                'status' => 'finalized',
                'created_by' => $marcus->id,
            ]
        );

        $conservativeOverrides = [];
        foreach ($fy27TargetModels as $target) {
            if (in_array($target->metric_key, ['sourced_revenue', 'influenced_revenue', 'pipeline_value'])) {
                $conservativeOverrides[] = [
                    'override_type' => 'target',
                    'reference_id' => $target->id,
                    'original_value' => $target->target_value,
                    'override_value' => round($target->target_value * 0.85, 2),
                ];
            }
        }

        foreach ($fy27BudgetModels as $budget) {
            if (in_array($budget->category, ['mdf', 'events', 'marketing'])) {
                $conservativeOverrides[] = [
                    'override_type' => 'budget',
                    'reference_id' => $budget->id,
                    'original_value' => $budget->planned_amount,
                    'override_value' => round($budget->planned_amount * 0.80, 2),
                ];
            }
        }

        foreach ($conservativeOverrides as $override) {
            StrategyScenarioOverride::updateOrCreate(
                ['strategy_scenario_id' => $conservative->id, 'override_type' => $override['override_type'], 'reference_id' => $override['reference_id']],
                $override
            );
        }

        $this->command->info('  ✓ Created "Conservative Approach" scenario (finalized)');

        // Scenario 3: APAC Expansion Focus
        $apac = StrategyScenario::updateOrCreate(
            ['name' => 'APAC Expansion Focus'],
            [
                'base_plan_id' => $fy27->id,
                'description' => 'Geographic reallocation scenario shifting 30% of MDF and enablement budget toward APAC markets. Expects slower initial returns but long-term ecosystem growth.',
                'status' => 'draft',
                'created_by' => $james->id,
            ]
        );

        // APAC scenario: boost partner counts, reallocate budgets
        foreach ($fy27TargetModels as $target) {
            if (in_array($target->metric_key, ['tier1_partners', 'tier2_partners'])) {
                StrategyScenarioOverride::updateOrCreate(
                    ['strategy_scenario_id' => $apac->id, 'override_type' => 'target', 'reference_id' => $target->id],
                    [
                        'original_value' => $target->target_value,
                        'override_value' => $target->target_value + 3,
                    ]
                );
            }
        }

        foreach ($fy27BudgetModels as $budget) {
            if (in_array($budget->category, ['mdf', 'enablement'])) {
                StrategyScenarioOverride::updateOrCreate(
                    ['strategy_scenario_id' => $apac->id, 'override_type' => 'budget', 'reference_id' => $budget->id],
                    [
                        'original_value' => $budget->planned_amount,
                        'override_value' => round($budget->planned_amount * 1.15, 2),
                    ]
                );
            }
            if ($budget->category === 'travel') {
                StrategyScenarioOverride::updateOrCreate(
                    ['strategy_scenario_id' => $apac->id, 'override_type' => 'budget', 'reference_id' => $budget->id],
                    [
                        'original_value' => $budget->planned_amount,
                        'override_value' => round($budget->planned_amount * 1.40, 2),
                    ]
                );
            }
        }

        $this->command->info('  ✓ Created "APAC Expansion Focus" scenario');

        // === Reviews ===
        StrategyPlanReview::updateOrCreate(
            ['strategy_plan_id' => $fy27->id, 'review_type' => 'monthly', 'review_date' => now()->subMonths(1)->startOfMonth()],
            [
                'status' => 'completed',
                'reviewed_by' => $sarah->id,
                'summary' => 'Strong Q1 performance driven by Accenture and Deloitte pipeline contributions. Sourced revenue ahead of plan by 33%. Certification completions tracking above target. MDF spend slightly above plan due to front-loaded event investments. Recommend maintaining current trajectory and monitoring Tier-2 conversion pipeline.',
                'findings' => [
                    ['category' => 'Revenue', 'status' => 'on_track', 'notes' => 'Sourced revenue at 133% of Q1 target. Influenced revenue also exceeding plan.'],
                    ['category' => 'Pipeline', 'status' => 'on_track', 'notes' => 'Pipeline value 85% above quarterly target. Strong deal flow from top 5 partners.'],
                    ['category' => 'Growth', 'status' => 'at_risk', 'notes' => 'Tier-1 count stable at 5. No new promotions in Q1. PwC closest to upgrade threshold.'],
                    ['category' => 'Enablement', 'status' => 'on_track', 'notes' => '78 completions vs 50 target. Bootcamp format driving higher attendance.'],
                    ['category' => 'Certification', 'status' => 'on_track', 'notes' => '45 active certs vs 35 Q1 target. New Anaplan platform cert seeing strong uptake.'],
                    ['category' => 'Delivery', 'status' => 'on_track', 'notes' => '18 projects delivered vs 12 target. CSAT slightly above target at 82.3%.'],
                    ['category' => 'Budget', 'status' => 'at_risk', 'notes' => 'MDF overspend of $180K in Q1. Need to moderate Q2 event commitments.'],
                ],
            ]
        );

        StrategyPlanReview::updateOrCreate(
            ['strategy_plan_id' => $fy27->id, 'review_type' => 'quarterly', 'review_date' => now()->subWeeks(3)],
            [
                'status' => 'completed',
                'reviewed_by' => $sarah->id,
                'summary' => 'End-of-Q1 quarterly review confirms strong start to FY27. Revenue metrics ahead of plan, but partner growth lagging. Budget discipline needed in Q2 after front-loaded MDF spending. Approved acceleration of Tier-1 promotion program for PwC and Capgemini. Escalated APAC expansion timeline discussion.',
                'findings' => [
                    ['category' => 'Revenue', 'status' => 'on_track', 'notes' => 'Both sourced and influenced revenue ahead of plan. Win rate at 32.5% vs 30% target.'],
                    ['category' => 'Pipeline', 'status' => 'on_track', 'notes' => 'Pipeline 85% above target. Quality metrics strong — avg deal size $172K.'],
                    ['category' => 'Growth', 'status' => 'behind', 'notes' => 'Zero net new Tier-1 partners. Approved fast-track program for PwC and Capgemini.'],
                    ['category' => 'Enablement', 'status' => 'on_track', 'notes' => 'Bootcamp ROI validated. Planning second cohort for Q2.'],
                    ['category' => 'Certification', 'status' => 'on_track', 'notes' => 'Strong momentum. Ramp time assumption (6 months) may be optimistic — actual trending at 7.5 months.'],
                    ['category' => 'Delivery', 'status' => 'on_track', 'notes' => '18 projects delivered. CSAT stable. Two at-risk projects from TCS flagged.'],
                    ['category' => 'Budget', 'status' => 'at_risk', 'notes' => 'Total Q1 spend $1.74M vs $1.44M plan. Need $300K savings in Q2-Q4.'],
                ],
            ]
        );

        StrategyPlanReview::updateOrCreate(
            ['strategy_plan_id' => $fy27->id, 'review_type' => 'monthly', 'review_date' => now()->addWeeks(2)->startOfMonth()],
            [
                'status' => 'scheduled',
                'reviewed_by' => $marcus->id,
            ]
        );

        $this->command->info('  ✓ Created 3 reviews (2 completed, 1 scheduled)');

        $this->command->info('✅ Strategy Workbench seeding complete!');
        $this->command->info('   CoE Users: jennifer.blake@example.com, kevin.nakamura@example.com, diana.costa@example.com, james.park@example.com');
        $this->command->info('   Plans: FY27 (active), FY28 (draft)');
        $this->command->info('   Scenarios: Aggressive Growth, Conservative Approach, APAC Expansion Focus');
    }
}
