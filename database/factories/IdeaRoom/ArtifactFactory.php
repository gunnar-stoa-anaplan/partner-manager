<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Artifact;
use App\Models\IdeaRoom\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artifact>
 */
class ArtifactFactory extends Factory
{
    protected $model = Artifact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'idea_room_id' => null,
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['outline', 'prd', 'note', 'document']),
            'title' => fake()->sentence(3),
            'content' => fake()->paragraphs(3, true),
            'file_path' => null,
            'file_mime' => null,
            'file_size' => null,
            'version' => 1,
            'metadata' => null,
        ];
    }

    public function file(): static
    {
        return $this->state(fn () => [
            'type' => 'file',
            'file_path' => 'artifacts/'.fake()->uuid().'.pdf',
            'file_mime' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 10485760),
        ]);
    }
}
