<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'display_name' => fake()->jobTitle(),
            'type' => 'internal',
            'is_protected' => false,
            'bypass_permissions' => false,
            'sort_order' => 0,
        ];
    }

    public function internal(): static
    {
        return $this->state(['type' => 'internal']);
    }

    public function portal(): static
    {
        return $this->state(['type' => 'portal']);
    }

    public function system(): static
    {
        return $this->state([
            'is_protected' => true,
            'bypass_permissions' => true,
        ]);
    }
}
