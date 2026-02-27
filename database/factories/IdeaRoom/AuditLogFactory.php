<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\AuditLog;
use App\Models\IdeaRoom\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'external_participant_id' => null,
            'actor_type' => 'user',
            'action' => fake()->randomElement(['idea.created', 'snapshot.shared', 'room.accessed', 'participant.invited']),
            'auditable_type' => 'App\\Models\\IdeaRoom\\Idea',
            'auditable_id' => Idea::factory(),
            'before' => null,
            'after' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
