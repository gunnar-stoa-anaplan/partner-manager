<?php

namespace Database\Factories;

use App\Models\Blueprint;
use App\Models\BlueprintOutcome;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlueprintOutcome>
 */
class BlueprintOutcomeFactory extends Factory
{
    protected $model = BlueprintOutcome::class;

    public function definition(): array
    {
        return [
            'blueprint_id' => Blueprint::factory(),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'title' => 'Partners understand '.$this->faker->words(4, true),
            'description' => $this->faker->sentence(8),
        ];
    }
}
