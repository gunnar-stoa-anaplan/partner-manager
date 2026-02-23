<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Room;
use App\Models\IdeaRoom\RoomMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomMessage>
 */
class RoomMessageFactory extends Factory
{
    protected $model = RoomMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_room_id' => Room::factory(),
            'sender_type' => 'user',
            'sender_id' => null,
            'content' => fake()->paragraph(),
            'metadata' => null,
        ];
    }

    public function fromAgent(): static
    {
        return $this->state(fn () => [
            'sender_type' => 'room_agent',
        ]);
    }

    public function fromCollaborator(): static
    {
        return $this->state(fn () => [
            'sender_type' => 'collaborator',
        ]);
    }
}
