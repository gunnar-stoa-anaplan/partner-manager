<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeeklyFocus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeeklyFocus>
 */
class WeeklyFocusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'week_start' => WeeklyFocus::currentWeekStart(),
            'summary' => fake()->optional()->sentence(),
            'manager_notes' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ];
    }

    public function forWeek(Carbon $date): static
    {
        return $this->state(fn () => [
            'week_start' => $date->startOfWeek(Carbon::MONDAY),
        ]);
    }

    public function reviewed(User $reviewer): static
    {
        return $this->state(fn () => [
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'manager_notes' => fake()->sentence(),
        ]);
    }
}
