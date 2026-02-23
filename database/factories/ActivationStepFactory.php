<?php

namespace Database\Factories;

use App\Enums\StepType;
use App\Models\ActivationPath;
use App\Models\ActivationStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivationStep>
 */
class ActivationStepFactory extends Factory
{
    protected $model = ActivationStep::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activation_path_id' => ActivationPath::factory(),
            'order' => fake()->numberBetween(1, 10),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'step_type' => fake()->randomElement(StepType::cases()),
            'criteria' => ['target' => fake()->numberBetween(1, 100)],
            'is_required' => true,
        ];
    }
}
