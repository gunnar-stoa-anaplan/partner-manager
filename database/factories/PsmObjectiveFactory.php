<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PsmObjective;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PsmObjective>
 */
class PsmObjectiveFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'partner_id' => Partner::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->sentence(),
            'category' => fake()->randomElement(array_keys(PsmObjective::CATEGORIES)),
            'metric_type' => 'manual',
            'metric_config' => null,
            'target_value' => fake()->numberBetween(1, 20),
            'baseline_value' => 0,
            'current_value' => 0,
            'target_date' => now()->addWeeks(fake()->numberBetween(1, 8)),
            'status' => 'active',
            'completed_at' => null,
            'suggested_by' => null,
            'source_signal' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'current_value' => $this->faker->numberBetween(5, 20),
            'completed_at' => now()->subDays(fake()->numberBetween(1, 14)),
        ])->afterMaking(function (PsmObjective $objective) {
            $objective->current_value = max($objective->current_value, $objective->target_value);
        });
    }

    public function missed(): static
    {
        return $this->state(fn () => [
            'status' => 'missed',
            'target_date' => now()->subDays(fake()->numberBetween(1, 14)),
        ]);
    }

    public function withBlueprint(int $blueprintId): static
    {
        return $this->state(fn () => [
            'metric_type' => 'blueprint_completions',
            'metric_config' => ['blueprint_id' => $blueprintId],
            'category' => 'enablement',
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn () => [
            'metric_type' => 'manual',
            'metric_config' => null,
        ]);
    }
}
