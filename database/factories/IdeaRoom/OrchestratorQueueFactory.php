<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\OrchestratorQueue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrchestratorQueue>
 */
class OrchestratorQueueFactory extends Factory
{
    protected $model = OrchestratorQueue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_type' => fake()->randomElement(['categorize', 'find_links', 'resurface', 'detect_duplicates']),
            'idea_id' => Idea::factory(),
            'priority' => 5,
            'payload' => null,
            'status' => 'pending',
            'attempts' => 0,
            'processed_at' => null,
        ];
    }
}
