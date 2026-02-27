<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Snapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Snapshot>
 */
class SnapshotFactory extends Factory
{
    protected $model = Snapshot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'idea_room_id' => null,
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'scope_config' => [
                'include_events' => true,
                'include_artifacts' => true,
                'include_room_messages' => false,
            ],
            'content_cache' => fake()->paragraphs(5, true),
            'access_token' => Str::random(64),
            'expires_at' => now()->addDays(30),
            'max_uses' => null,
            'use_count' => 0,
            'is_active' => true,
        ];
    }
}
