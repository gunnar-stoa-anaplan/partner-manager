<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerGoal;
use App\Models\PartnerGoalDimension;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerGoalSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating partner goals and dimensions...');

        $partners = Partner::all();
        $admin = User::whereRelation('role', 'slug', 'admin')->first();
        $goalCount = 0;
        $dimCount = 0;

        // Anaplan FY starts in Feb — FY27 = Feb 2026 – Jan 2027
        $fiscalYears = [2027, 2028];

        foreach ($partners as $partner) {
            foreach ($fiscalYears as $fy) {
                // Skip FY28 for ~40% of partners (not all have next year targets yet)
                if ($fy === 2028 && fake()->boolean(40)) {
                    continue;
                }

                $sourcedTarget = fake()->randomFloat(2, 200000, 3000000);
                $influencedTarget = fake()->randomFloat(2, 500000, 8000000);

                $goal = PartnerGoal::create([
                    'partner_id' => $partner->id,
                    'fiscal_year' => $fy,
                    'sourced_nnacv_target' => $sourcedTarget,
                    'influenced_nnacv_target' => $influencedTarget,
                    'notes' => fake()->boolean(30) ? fake()->sentence(8) : null,
                    'updated_by' => $admin->id,
                ]);
                $goalCount++;

                // Create goal dimensions
                $dimensions = [
                    ['dimension' => 'sourced_nnacv', 'target' => $sourcedTarget, 'unit' => 'currency', 'label' => 'Sourced NNACV'],
                    ['dimension' => 'influenced_nnacv', 'target' => $influencedTarget, 'unit' => 'currency', 'label' => 'Influenced NNACV'],
                    ['dimension' => 'certification_mb', 'target' => fake()->numberBetween(3, 15), 'unit' => 'count', 'label' => 'Model Builder Certifications'],
                    ['dimension' => 'certification_sa', 'target' => fake()->numberBetween(1, 8), 'unit' => 'count', 'label' => 'Solution Architect Certifications'],
                    ['dimension' => 'projects_delivered', 'target' => fake()->numberBetween(4, 20), 'unit' => 'count', 'label' => 'Projects Delivered'],
                    ['dimension' => 'projects_ontime_pct', 'target' => fake()->randomFloat(0, 80, 95), 'unit' => 'percentage', 'label' => 'Projects On-Time %'],
                    ['dimension' => 'enablement_sessions', 'target' => fake()->numberBetween(6, 24), 'unit' => 'count', 'label' => 'Enablement Sessions'],
                    ['dimension' => 'win_rate', 'target' => fake()->randomFloat(0, 25, 50), 'unit' => 'percentage', 'label' => 'Win Rate'],
                ];

                foreach ($dimensions as $dim) {
                    $target = $dim['target'];

                    // For FY27 (current year), generate partial actuals
                    $actualValue = null;
                    if ($fy === 2027) {
                        $progressPct = fake()->randomFloat(2, 0.1, 0.85);
                        $actualValue = round($target * $progressPct, 2);
                    }

                    // Split target across quarters (not perfectly even — realistic)
                    $q1Pct = fake()->randomFloat(2, 0.15, 0.30);
                    $q2Pct = fake()->randomFloat(2, 0.20, 0.35);
                    $q3Pct = fake()->randomFloat(2, 0.20, 0.35);
                    $q4Pct = 1 - $q1Pct - $q2Pct - $q3Pct;

                    PartnerGoalDimension::create([
                        'partner_goal_id' => $goal->id,
                        'dimension' => $dim['dimension'],
                        'target_value' => $target,
                        'actual_value' => $actualValue,
                        'unit' => $dim['unit'],
                        'label' => $dim['label'],
                        'q1_target' => round($target * $q1Pct, 2),
                        'q2_target' => round($target * $q2Pct, 2),
                        'q3_target' => round($target * $q3Pct, 2),
                        'q4_target' => round($target * $q4Pct, 2),
                    ]);
                    $dimCount++;
                }
            }
        }

        $this->command->info("Created {$goalCount} goals, {$dimCount} dimensions.");
    }
}
