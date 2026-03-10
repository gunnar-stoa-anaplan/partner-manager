<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\WeeklyFocus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeeklyFocusItem>
 */
class WeeklyFocusItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'weekly_focus_id' => WeeklyFocus::factory(),
            'partner_id' => Partner::factory(),
            'description' => fake()->sentence(),
            'status' => 'planned',
            'completed_at' => null,
            'sort_order' => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => 'in_progress',
        ]);
    }

    public function dropped(): static
    {
        return $this->state(fn () => [
            'status' => 'dropped',
        ]);
    }
}
