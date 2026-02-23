<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\AgentMemory;
use App\Models\IdeaRoom\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentMemory>
 */
class AgentMemoryFactory extends Factory
{
    protected $model = AgentMemory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_room_id' => Room::factory(),
            'memory_type' => fake()->randomElement(['fact', 'decision', 'assumption', 'open_question']),
            'content' => fake()->sentence(),
            'importance' => fake()->numberBetween(1, 10),
            'source_message_id' => null,
            'embedding_id' => null,
            'is_active' => true,
        ];
    }
}
