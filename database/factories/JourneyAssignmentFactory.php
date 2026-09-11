<?php

namespace Database\Factories;

use App\Models\Journey;
use App\Models\JourneyAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JourneyAssignment>
 */
class JourneyAssignmentFactory extends Factory
{
    protected $model = JourneyAssignment::class;

    public function definition(): array
    {
        return [
            'journey_id' => Journey::factory(),
            'user_id' => User::factory(),
        ];
    }
}
