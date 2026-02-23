<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\IdeaEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdeaEvent>
 */
class IdeaEventFactory extends Factory
{
    protected $model = IdeaEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'user_id' => User::factory(),
            'event_type' => fake()->randomElement(['created', 'note_added', 'bucket_assigned', 'state_changed', 'tag_added']),
            'source' => fake()->randomElement(['user', 'orchestrator', 'room_agent']),
            'payload' => ['text' => fake()->sentence()],
        ];
    }
}
