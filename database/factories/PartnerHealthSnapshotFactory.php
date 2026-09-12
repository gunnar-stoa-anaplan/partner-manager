<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerHealthSnapshotFactory extends Factory
{
    protected $model = PartnerHealthSnapshot::class;

    public function definition(): array
    {
        $score = $this->faker->randomFloat(1, 0, 100);

        return [
            'partner_id' => Partner::factory(),
            'score' => $score,
            'risk_level' => match (true) {
                $score >= 70 => 'healthy',
                $score >= 50 => 'watch',
                $score >= 30 => 'at_risk',
                default => 'critical',
            },
            'signals' => [],
            'dimensions' => [
                'scorecard' => $this->faker->randomFloat(1, 0, 100),
                'pipeline_trend' => $this->faker->randomFloat(1, 0, 100),
                'goal_attainment' => $this->faker->randomFloat(1, 0, 100),
            ],
            // Unique per (partner_id, snapshot_date); a random offset keeps
            // multiple snapshots for the same partner from colliding.
            'snapshot_date' => now()->subDays($this->faker->unique()->numberBetween(0, 60)),
        ];
    }
}
