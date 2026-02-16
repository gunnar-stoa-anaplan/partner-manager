<?php

namespace Database\Factories;

use App\Models\Blueprint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blueprint>
 */
class BlueprintFactory extends Factory
{
    protected $model = Blueprint::class;

    public function definition(): array
    {
        $category = $this->faker->randomElement(array_keys(Blueprint::CATEGORIES));
        $productArea = $this->faker->randomElement(Blueprint::PRODUCT_AREAS);

        return [
            'name' => $this->faker->words(3, true).' Enablement',
            'category' => $category,
            'product_area' => $productArea,
            'description' => $this->faker->sentence(12),
            'version' => 'V'.$this->faker->numberBetween(1, 3).'.'.$this->faker->numberBetween(0, 9),
            'status' => 'draft',
            'created_by' => User::factory(),
            'owner_id' => null,
            'drafted_at' => now(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    public function withOwner(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $user ?? User::factory(),
        ]);
    }

    public function application(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'application',
        ]);
    }

    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'product',
        ]);
    }

    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'delivery',
        ]);
    }
}
