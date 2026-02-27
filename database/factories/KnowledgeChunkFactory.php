<?php

namespace Database\Factories;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeChunk>
 */
class KnowledgeChunkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => KnowledgeDocument::factory(),
            'chunk_index' => fake()->numberBetween(0, 50),
            'content' => fake()->paragraphs(3, true),
            'embedding' => array_map(fn () => fake()->randomFloat(6, -1, 1), range(1, 1536)),
            'word_count' => fake()->numberBetween(100, 750),
            'start_page' => fake()->numberBetween(1, 100),
        ];
    }
}
