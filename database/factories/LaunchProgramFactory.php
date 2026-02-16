<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LaunchProgramFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true).' Launch Program';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'product_id' => Product::factory(),
            'description' => fake()->paragraph(),
            'status' => 'draft',
            'starts_at' => now()->addDays(rand(7, 30)),
            'target_ga_date' => now()->addMonths(rand(3, 6)),
            'ends_at' => now()->addMonths(rand(6, 12)),
            'max_partners' => fake()->optional(0.7)->numberBetween(5, 20),
            'partner_strategy' => 'include',
            'created_by' => User::factory(),
            'owner_id' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function acceptingNominations(): static
    {
        return $this->state(fn () => ['status' => 'accepting_nominations']);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'starts_at' => now()->subDays(rand(7, 60)),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'starts_at' => now()->subMonths(rand(6, 12)),
            'ends_at' => now()->subDays(rand(1, 30)),
        ]);
    }
}
