<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Edge;
use App\Models\IdeaRoom\Idea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Edge>
 */
class EdgeFactory extends Factory
{
    protected $model = Edge::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_idea_id' => Idea::factory(),
            'target_idea_id' => Idea::factory(),
            'edge_type' => fake()->randomElement(['related_to', 'extends', 'conflicts_with', 'duplicates']),
            'status' => 'proposed',
            'proposed_by' => 'orchestrator',
            'confidence' => fake()->randomFloat(2, 0.5, 1.0),
            'reason' => fake()->sentence(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }
}
