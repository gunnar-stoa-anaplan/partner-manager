<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'status' => 'active',
            'agent_persona' => 'general',
            'agent_config' => null,
        ];
    }

    public function paused(): static
    {
        return $this->state(fn () => [
            'status' => 'paused',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => 'archived',
        ]);
    }
}
