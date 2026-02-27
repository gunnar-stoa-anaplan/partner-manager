<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkshopGuide;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopGuide>
 */
class WorkshopGuideFactory extends Factory
{
    protected $model = WorkshopGuide::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(4, true).' Workshop';

        return [
            'enablement_event_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(2),
            'navigation' => [
                'Getting Started' => [
                    'Workshop Overview' => 'overview',
                    'Environment Setup' => 'setup',
                ],
                'Labs' => [
                    'Lab 1: Foundation' => 'lab-1-foundation',
                    'Lab 2: Configuration' => 'lab-2-configuration',
                ],
            ],
            'product_area' => fake()->randomElement(['IFP', 'SCM', 'SPM', 'OWP']),
            'status' => 'draft',
            'requires_auth' => false,
            'published_at' => null,
            'estimated_duration_hours' => fake()->randomElement([2, 4, 8, 16]),
            'created_by' => User::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => 'archived',
            'published_at' => now()->subMonths(rand(1, 6)),
        ]);
    }

    public function requiresAuth(): static
    {
        return $this->state(fn () => ['requires_auth' => true]);
    }
}
