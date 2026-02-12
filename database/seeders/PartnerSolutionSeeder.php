<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerSolution;
use App\Models\SolutionStageHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerSolutionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating partner solutions and stage history...');

        $partners = Partner::all();
        $users = User::all();
        $admin = User::where('role', 'admin')->first();
        $solutionCount = 0;
        $historyCount = 0;

        $solutionNames = [
            'IFP' => ['Integrated Financial Planner', 'FP&A Accelerator', 'Revenue Forecasting Suite', 'Budget Consolidation Pack', 'Cash Flow Optimizer'],
            'SCM' => ['Supply Chain Command Center', 'Demand Planning Accelerator', 'S&OP Dashboard', 'Inventory Optimizer', 'Procurement Analytics'],
            'SPM' => ['Territory & Quota Manager', 'Incentive Compensation Planner', 'Sales Analytics Dashboard', 'Quota Allocation Engine'],
            'Polaris' => ['Polaris Migration Toolkit', 'Polaris Accelerator Pack', 'Model Conversion Utility', 'Polaris Best Practice Starter'],
            'OWP' => ['Operational Workforce Planner', 'Headcount Forecaster', 'Skills Capacity Planner'],
        ];

        $industries = ['Financial Services', 'Manufacturing', 'Technology', 'Healthcare', 'Retail', 'Energy', 'Cross-Industry'];
        $lifecycleStages = ['build', 'market', 'sell', 'grow'];

        foreach ($partners as $partner) {
            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;

            // Each partner has 1-4 solutions
            $numSolutions = fake()->numberBetween(1, 4);

            for ($s = 0; $s < $numSolutions; $s++) {
                $productArea = fake()->randomElement(array_keys($solutionNames));
                $name = fake()->randomElement($solutionNames[$productArea]);

                // Weighted toward earlier stages
                $stageIndex = fake()->randomElement(array_merge(
                    array_fill(0, 4, 0), // build
                    array_fill(0, 3, 1), // market
                    array_fill(0, 2, 2), // sell
                    array_fill(0, 1, 3), // grow
                ));

                $currentStage = $lifecycleStages[$stageIndex];
                $stageEnteredAt = fake()->dateTimeBetween('-6 months', '-1 week');

                $solution = PartnerSolution::create([
                    'partner_id' => $partner->id,
                    'name' => $name . ' — ' . $partner->name,
                    'description' => fake()->paragraph(2),
                    'product_area' => $productArea,
                    'industry_focus' => fake()->randomElement($industries),
                    'lifecycle_stage' => $currentStage,
                    'stage_entered_at' => $stageEnteredAt,
                    'status' => fake()->randomElement(['active', 'active', 'active', 'paused']),
                    'demo_url' => fake()->boolean(40) ? 'https://demo.example.com/' . fake()->slug(3) : null,
                    'documentation_url' => fake()->boolean(50) ? 'https://docs.example.com/' . fake()->slug(3) : null,
                    'estimated_acv' => fake()->randomFloat(2, 50000, 500000),
                    'deployment_duration_weeks' => fake()->numberBetween(4, 20),
                    'created_by' => $psm->id,
                    'notes' => fake()->boolean(25) ? fake()->sentence(6) : null,
                ]);
                $solutionCount++;

                // Generate stage history for all stages up to current
                $previousDate = fake()->dateTimeBetween('-18 months', $stageEnteredAt);
                for ($h = 0; $h <= $stageIndex; $h++) {
                    $fromStage = $h === 0 ? null : $lifecycleStages[$h - 1];
                    $toStage = $lifecycleStages[$h];

                    $transitionDate = $h === $stageIndex
                        ? $stageEnteredAt
                        : fake()->dateTimeBetween($previousDate, $stageEnteredAt);

                    SolutionStageHistory::create([
                        'partner_solution_id' => $solution->id,
                        'from_stage' => $fromStage,
                        'to_stage' => $toStage,
                        'transitioned_at' => $transitionDate,
                        'transitioned_by' => $psm->id,
                        'notes' => fake()->randomElement([
                            'Solution created and development started',
                            'Development complete — moving to marketing phase',
                            'GTM materials ready — entering sales phase',
                            'First customer deployments successful — scaling',
                            null,
                        ]),
                    ]);
                    $historyCount++;

                    $previousDate = $transitionDate;
                }
            }
        }

        $this->command->info("Created {$solutionCount} solutions, {$historyCount} stage history records.");
    }
}
