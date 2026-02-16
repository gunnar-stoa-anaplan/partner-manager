<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerTaskFactory extends Factory
{
    protected $model = PartnerTask::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'title' => $this->faker->sentence(4),
            'category' => $this->faker->randomElement(array_keys(\App\Models\TaskTemplate::CATEGORIES)),
            'requirement_level' => 'required',
            'status' => 'not_started',
            'priority' => 'medium',
            'effort_size' => 'medium',
            'owner' => 'psm',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_started',
            'due_date' => now()->subDays(7),
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'requirement_level' => 'optional',
        ]);
    }
}
