<?php

namespace Database\Factories;

use App\Models\LaunchProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaunchProgramDateChangeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'launch_program_id' => LaunchProgram::factory(),
            'field' => fake()->randomElement(['starts_at', 'target_ga_date', 'ends_at']),
            'old_date' => now(),
            'new_date' => now()->addDays(rand(7, 30)),
            'reason' => fake()->sentence(),
            'changed_by' => User::factory(),
        ];
    }
}
