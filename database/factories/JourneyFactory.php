<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Journey>
 */
class JourneyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Journey',
            'journey_key' => fake()->unique()->slug(),
            'product_area' => fake()->words(2, true),
            'persona' => fake()->randomElement(['Delivery', 'Sales', 'Technical']),
            'status' => 'pilot',
            'db_path' => '/tmp/nonexistent_journey_state.db',
            'config_path' => null,
        ];
    }

    /**
     * Set status to active.
     */
    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    /**
     * Set status to planned.
     */
    public function planned(): static
    {
        return $this->state(['status' => 'planned']);
    }
}
