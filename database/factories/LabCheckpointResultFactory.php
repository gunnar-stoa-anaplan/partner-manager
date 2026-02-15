<?php

namespace Database\Factories;

use App\Models\LabCheckpoint;
use App\Models\LabParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LabCheckpointResult>
 */
class LabCheckpointResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lab_participant_id' => LabParticipant::factory(),
            'lab_checkpoint_id' => LabCheckpoint::factory(),
            'passed' => false,
            'validated_at' => now(),
            'validation_details' => null,
            'error_message' => null,
            'attempt_number' => 1,
        ];
    }

    public function passed(): static
    {
        return $this->state(fn () => [
            'passed' => true,
            'error_message' => null,
            'validation_details' => ['match' => true],
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'passed' => false,
            'error_message' => fake()->randomElement([
                'Formula does not match expected value.',
                'Module not found in participant model.',
                'List items differ from expected.',
                'Cell value outside acceptable tolerance.',
                'Line item format does not match expected configuration.',
            ]),
            'validation_details' => [
                'match' => false,
                'expected' => 'SUM(Revenue)',
                'actual' => 'Revenue',
            ],
        ]);
    }
}
