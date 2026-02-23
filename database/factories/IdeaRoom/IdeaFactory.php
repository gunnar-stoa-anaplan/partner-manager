<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Idea>
 */
class IdeaFactory extends Factory
{
    protected $model = Idea::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'original_text' => fake()->paragraph(),
            'lifecycle_state' => 'spark',
            'last_activity_at' => now(),
            'metadata' => null,
        ];
    }

    public function growing(): static
    {
        return $this->state(fn () => [
            'lifecycle_state' => 'growing',
        ]);
    }

    public function developed(): static
    {
        return $this->state(fn () => [
            'lifecycle_state' => 'developed',
        ]);
    }

    public function dormant(): static
    {
        return $this->state(fn () => [
            'lifecycle_state' => 'dormant',
            'dormant_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'lifecycle_state' => 'archived',
        ]);
    }

    public function dropped(): static
    {
        return $this->state(fn () => [
            'lifecycle_state' => 'dropped',
        ]);
    }
}
