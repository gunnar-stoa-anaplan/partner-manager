<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopSession>
 */
class WorkshopSessionFactory extends Factory
{
    protected $model = WorkshopSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+3 months');

        return [
            'workshop_id' => Workshop::factory(),
            'slug' => fake()->unique()->slug(),
            'name' => fake()->words(2, true).' Cohort',
            'start_date' => $start,
            'end_date' => (clone $start)->modify('+4 days'),
            'status' => 'upcoming',
            'created_by' => User::factory(),
        ];
    }
}
