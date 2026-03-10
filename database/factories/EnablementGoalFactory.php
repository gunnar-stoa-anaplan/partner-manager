<?php

namespace Database\Factories;

use App\Models\EnablementGoal;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnablementGoal>
 */
class EnablementGoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'user_id' => User::factory(),
            'category' => fake()->randomElement(EnablementGoal::categoryKeys()),
            'target' => fake()->numberBetween(50, 200),
            'baseline' => fake()->numberBetween(20, 100),
        ];
    }
}
