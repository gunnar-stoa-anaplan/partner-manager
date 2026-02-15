<?php

namespace Database\Factories;

use App\Models\LaunchProgram;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaunchProgramPartnerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'launch_program_id' => LaunchProgram::factory(),
            'partner_id' => Partner::factory(),
            'status' => 'nominated',
            'nominated_by' => User::factory(),
            'accepted_at' => null,
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function nominated(): static
    {
        return $this->state(fn () => ['status' => 'nominated']);
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => 'accepted',
            'accepted_at' => now()->subDays(rand(1, 14)),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'accepted_at' => now()->subDays(rand(14, 60)),
        ]);
    }
}
