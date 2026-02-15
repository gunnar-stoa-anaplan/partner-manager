<?php

namespace Database\Factories;

use App\Models\LaunchProgramMilestone;
use App\Models\LaunchProgramParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaunchProgramMilestoneCompletionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'launch_program_participant_id' => LaunchProgramParticipant::factory(),
            'launch_program_milestone_id' => LaunchProgramMilestone::factory(),
            'completed_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'evidence_url' => fake()->optional(0.4)->url(),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
