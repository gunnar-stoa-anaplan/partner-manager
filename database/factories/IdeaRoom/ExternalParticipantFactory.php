<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\ExternalParticipant;
use App\Models\IdeaRoom\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExternalParticipant>
 */
class ExternalParticipantFactory extends Factory
{
    protected $model = ExternalParticipant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_room_id' => Room::factory(),
            'invited_by' => User::factory(),
            'participant_type' => 'person',
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'agent_endpoint' => null,
            'agent_auth_token' => null,
            'permissions' => [
                'can_read' => true,
                'can_write' => true,
                'can_create_artifacts' => false,
                'can_see_history' => false,
            ],
            'snapshot_id' => null,
            'status' => 'invited',
            'accepted_at' => null,
            'revoked_at' => null,
        ];
    }

    public function agent(): static
    {
        return $this->state(fn () => [
            'participant_type' => 'agent',
            'agent_endpoint' => fake()->url(),
            'agent_auth_token' => Str::random(64),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'accepted_at' => now(),
        ]);
    }
}
