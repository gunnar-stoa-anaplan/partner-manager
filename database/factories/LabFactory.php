<?php

namespace Database\Factories;

use App\Models\AnaplanModel;
use App\Models\EnablementSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lab>
 */
class LabFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true).' Lab';

        return [
            'enablement_session_id' => EnablementSession::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'instructions' => fake()->paragraphs(2, true),
            'golden_model_id' => AnaplanModel::factory(),
            'golden_snapshot' => null,
            'status' => 'draft',
            'published_at' => null,
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90, 120]),
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
            'golden_snapshot' => [
                'captured_at' => now()->subDays(rand(1, 30))->toIso8601String(),
                'modules' => [],
                'lists' => [],
            ],
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => 'archived',
            'published_at' => now()->subMonths(rand(1, 6)),
        ]);
    }
}
