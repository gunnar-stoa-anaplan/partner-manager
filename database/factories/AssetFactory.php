<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(array_keys(Asset::TYPES));

        return [
            'type' => $type,
            'name' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'url' => fake()->url(),
            'external_id' => fake()->unique()->bothify('??##??##'),
            'duration_minutes' => fake()->randomElement([30, 60, 90, 120, 180, 240, 480]),
            'tags' => fake()->randomElements(['ifp', 'finance', 'modeling', 'sales', 'delivery', 'foundation', 'advanced'], 3),
            'metadata' => null,
            'status' => 'active',
            'scope' => 'global',
        ];
    }
}
