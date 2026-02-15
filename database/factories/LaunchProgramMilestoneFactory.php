<?php

namespace Database\Factories;

use App\Models\LaunchProgram;
use App\Models\LaunchProgramMilestone;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaunchProgramMilestoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'launch_program_id' => LaunchProgram::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.6)->paragraph(),
            'sort_order' => fake()->numberBetween(1, 10),
            'due_date' => fake()->optional(0.5)->dateTimeBetween('+1 week', '+3 months'),
            'is_required' => fake()->boolean(80),
            'type' => fake()->randomElement(array_keys(LaunchProgramMilestone::TYPES)),
        ];
    }

    public function required(): static
    {
        return $this->state(fn () => ['is_required' => true]);
    }

    public function optional(): static
    {
        return $this->state(fn () => ['is_required' => false]);
    }
}
