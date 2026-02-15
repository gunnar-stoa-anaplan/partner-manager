<?php

namespace Database\Factories;

use App\Models\AnaplanModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnaplanVersionConfig>
 */
class AnaplanVersionConfigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_area' => fake()->unique()->randomElement(['IFP', 'Finance']),
            'current_version' => fake()->randomElement(array_filter(AnaplanModel::VERSIONS, fn ($v) => ! in_array($v, ['Other', 'Demo', 'Base']))),
        ];
    }
}
