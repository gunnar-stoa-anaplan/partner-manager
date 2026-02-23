<?php

namespace Database\Factories;

use App\Models\ActivationStep;
use App\Models\StepProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StepProgress>
 */
class StepProgressFactory extends Factory
{
    protected $model = StepProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $target = fake()->numberBetween(1, 100);
        $current = fake()->randomFloat(2, 0, $target);

        return [
            'activation_step_id' => ActivationStep::factory(),
            'user_id' => User::factory(),
            'current_value' => $current,
            'target_value' => $target,
            'completed' => $current >= $target,
            'completed_at' => $current >= $target ? now() : null,
            'metadata' => null,
        ];
    }
}
