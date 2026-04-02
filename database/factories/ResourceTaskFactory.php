<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\ResourceTask;
use App\Models\ResourceTaskTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceTaskFactory extends Factory
{
    protected $model = ResourceTask::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'partner_id' => Partner::factory(),
            'title' => $this->faker->sentence(4),
            'category' => $this->faker->randomElement(array_keys(ResourceTaskTemplate::CATEGORIES)),
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
