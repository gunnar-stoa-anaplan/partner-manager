<?php

namespace Database\Factories;

use App\Models\AnaplanModel;
use App\Models\Contact;
use App\Models\Lab;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LabParticipant>
 */
class LabParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lab_id' => Lab::factory(),
            'contact_id' => Contact::factory(),
            'partner_id' => Partner::factory(),
            'assigned_model_id' => AnaplanModel::factory(),
            'status' => 'not_started',
            'started_at' => null,
            'completed_at' => null,
            'total_points_earned' => 0,
            'total_points_possible' => 0,
            'enrolled_by' => User::factory(),
        ];
    }

    public function notStarted(): static
    {
        return $this->state(fn () => ['status' => 'not_started']);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => 'in_progress',
            'started_at' => now()->subMinutes(rand(10, 120)),
            'total_points_earned' => rand(1, 5),
            'total_points_possible' => 10,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'started_at' => now()->subHours(rand(1, 4)),
            'completed_at' => now()->subMinutes(rand(1, 30)),
            'total_points_earned' => 10,
            'total_points_possible' => 10,
        ]);
    }
}
