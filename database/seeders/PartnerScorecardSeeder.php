<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerScorecard;
use App\Models\ScorecardWeight;
use Illuminate\Database\Seeder;

class PartnerScorecardSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating partner scorecards...');

        $partners = Partner::all();
        $weights = ScorecardWeight::pluck('weight', 'dimension')->toArray();
        $count = 0;

        // Default weights if none configured
        if (empty($weights)) {
            $weights = [
                'certification' => 0.15,
                'pipeline' => 0.20,
                'delivery' => 0.20,
                'engagement' => 0.15,
                'growth' => 0.20,
                'self_sufficiency' => 0.10,
            ];
        }

        foreach ($partners as $partner) {
            // Generate 3-6 monthly scorecards over last 6 months
            $scorecardCount = fake()->numberBetween(3, 6);
            $baseDate = now()->subMonths($scorecardCount);

            // Each partner has a "personality" — base performance level
            $baseLevel = fake()->randomElement(['strong', 'strong', 'average', 'average', 'average', 'weak']);
            $baseRange = match ($baseLevel) {
                'strong' => [70, 95],
                'average' => [45, 75],
                'weak' => [20, 55],
            };

            for ($m = 0; $m < $scorecardCount; $m++) {
                $scoredAt = $baseDate->copy()->addMonths($m)->format('Y-m-d');

                // Slight improvement trend over time
                $trend = $m * fake()->randomFloat(1, 0.5, 2.0);

                $certScore = min(100, fake()->randomFloat(1, ...$baseRange) + $trend);
                $pipelineScore = min(100, fake()->randomFloat(1, ...$baseRange) + $trend);
                $deliveryScore = min(100, fake()->randomFloat(1, ...$baseRange) + $trend);
                $engagementScore = min(100, fake()->randomFloat(1, ...$baseRange) + $trend);
                $growthScore = min(100, fake()->randomFloat(1, ...$baseRange) + $trend);
                $selfSuffScore = min(100, fake()->randomFloat(1, ...$baseRange) + $trend);

                $overallScore =
                    ($certScore * ($weights['certification'] ?? 0.15)) +
                    ($pipelineScore * ($weights['pipeline'] ?? 0.20)) +
                    ($deliveryScore * ($weights['delivery'] ?? 0.20)) +
                    ($engagementScore * ($weights['engagement'] ?? 0.15)) +
                    ($growthScore * ($weights['growth'] ?? 0.20)) +
                    ($selfSuffScore * ($weights['self_sufficiency'] ?? 0.10));

                $tier = match (true) {
                    $overallScore >= 80 => 'Platinum',
                    $overallScore >= 60 => 'Gold',
                    $overallScore >= 40 => 'Silver',
                    default => 'Bronze',
                };

                PartnerScorecard::create([
                    'partner_id' => $partner->id,
                    'scored_at' => $scoredAt,
                    'overall_score' => round($overallScore, 1),
                    'certification_score' => round($certScore, 1),
                    'pipeline_score' => round($pipelineScore, 1),
                    'delivery_score' => round($deliveryScore, 1),
                    'engagement_score' => round($engagementScore, 1),
                    'growth_score' => round($growthScore, 1),
                    'self_sufficiency_score' => round($selfSuffScore, 1),
                    'tier' => $tier,
                    'gate_eligible' => $overallScore >= 60 && $certScore >= 50 && $deliveryScore >= 50,
                    'score_details' => [
                        'weights_used' => $weights,
                        'scoring_method' => 'weighted_average',
                        'data_sources' => ['certifications', 'opportunities', 'projects', 'events', 'goals'],
                    ],
                    'scoring_config' => [
                        'version' => '1.0',
                        'auto_scored' => true,
                    ],
                ]);
                $count++;
            }
        }

        $this->command->info("Created {$count} scorecards.");
    }
}
