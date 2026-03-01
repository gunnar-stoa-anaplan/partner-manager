<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'icon' => 'box',
            'group' => 'core',
            'is_active' => true,
            'sort_order' => 0,
            'definition' => ['sections' => [], 'extends' => []],
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withSections(array $sections = []): static
    {
        return $this->state([
            'definition' => [
                'sections' => $sections ?: [
                    'default' => [
                        'label' => 'Default',
                        'icon' => 'box',
                        'items' => [],
                    ],
                ],
                'extends' => [],
            ],
        ]);
    }
}
