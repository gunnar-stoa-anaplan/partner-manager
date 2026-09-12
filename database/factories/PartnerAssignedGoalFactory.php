<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerAssignedGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerAssignedGoalFactory extends Factory
{
    protected $model = PartnerAssignedGoal::class;

    public function definition(): array
    {
        $target = $this->faker->randomFloat(2, 1, 1000);

        return [
            'partner_id' => Partner::factory(),
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement(['pipeline', 'certifications', 'delivery', 'enablement']),
            'metric_key' => $this->faker->unique()->slug(2),
            'unit' => $this->faker->randomElement(['currency', 'count', 'percentage']),
            'cadence' => 'annual',
            'fiscal_year' => 'FY'.$this->faker->numberBetween(25, 28),
            'target_value' => $target,
            'actual_value' => $this->faker->randomFloat(2, 0, $target),
            'status' => $this->faker->randomElement(array_keys(PartnerAssignedGoal::STATUSES)),
            'assigned_by' => User::factory(),
        ];
    }
}
