<?php

namespace Database\Factories;

use App\Models\BlueprintContentItem;
use App\Models\BlueprintOutcome;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlueprintContentItem>
 */
class BlueprintContentItemFactory extends Factory
{
    protected $model = BlueprintContentItem::class;

    public function definition(): array
    {
        return [
            'blueprint_outcome_id' => BlueprintOutcome::factory(),
            'sort_order' => $this->faker->numberBetween(1, 20),
            'title' => $this->faker->sentence(4),
            'url' => $this->faker->optional(0.7)->url(),
            'duration_minutes' => $this->faker->numberBetween(10, 120),
            'duration_display' => $this->faker->randomElement(['30 min', '1 hour', '2 hours', '45 min']),
            'source' => $this->faker->randomElement(BlueprintContentItem::SOURCES),
            'prerequisites' => $this->faker->optional(0.3)->sentence(5),
            'gtm_applicable' => $this->faker->randomElement(['required', 'optional', 'na']),
            'pre_sales_applicable' => $this->faker->randomElement(['required', 'optional', 'na']),
            'delivery_applicable' => $this->faker->randomElement(['required', 'optional', 'na']),
        ];
    }

    public function requiredForAll(): static
    {
        return $this->state(fn (array $attributes) => [
            'gtm_applicable' => 'required',
            'pre_sales_applicable' => 'required',
            'delivery_applicable' => 'required',
        ]);
    }

    public function gtmOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'gtm_applicable' => 'required',
            'pre_sales_applicable' => 'na',
            'delivery_applicable' => 'na',
        ]);
    }

    public function deliveryOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'gtm_applicable' => 'na',
            'pre_sales_applicable' => 'na',
            'delivery_applicable' => 'required',
        ]);
    }
}
