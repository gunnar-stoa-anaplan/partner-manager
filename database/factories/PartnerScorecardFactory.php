<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerScorecard;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerScorecardFactory extends Factory
{
    protected $model = PartnerScorecard::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'scored_at' => now()->subDays($this->faker->numberBetween(0, 30)),
            'overall_score' => $this->faker->randomFloat(1, 0, 100),
            'certification_score' => $this->faker->randomFloat(1, 0, 100),
            'pipeline_score' => $this->faker->randomFloat(1, 0, 100),
            'delivery_score' => $this->faker->randomFloat(1, 0, 100),
            'engagement_score' => $this->faker->randomFloat(1, 0, 100),
            'growth_score' => $this->faker->randomFloat(1, 0, 100),
            'self_sufficiency_score' => $this->faker->randomFloat(1, 0, 100),
            'tier' => $this->faker->randomElement(['bronze', 'silver', 'gold', 'platinum']),
            'gate_eligible' => $this->faker->boolean(),
        ];
    }
}
