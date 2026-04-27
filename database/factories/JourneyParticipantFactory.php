<?php

namespace Database\Factories;

use App\Models\Journey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JourneyParticipant>
 */
class JourneyParticipantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'journey_id' => Journey::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'external_id' => fake()->unique()->uuid(),
            'current_stage' => 1,
            'enrolled_at' => now(),
            'last_active_at' => now(),
            'metadata' => null,
        ];
    }
}
