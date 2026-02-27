<?php

namespace Database\Factories;

use App\Enums\DocumentSource;
use App\Enums\DocumentStatus;
use App\Models\KnowledgeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeDocument>
 */
class KnowledgeDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->word().'.pdf';

        return [
            'title' => fake()->sentence(4),
            'file_name' => $fileName,
            'file_path' => 'knowledge-base/'.$fileName,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => fake()->numberBetween(1024, 10485760),
            'source' => DocumentSource::Upload,
            'status' => DocumentStatus::Completed,
            'chunk_count' => fake()->numberBetween(5, 50),
            'checksum' => fake()->sha256(),
            'ingested_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => DocumentStatus::Pending,
            'chunk_count' => 0,
            'ingested_at' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn () => [
            'status' => DocumentStatus::Processing,
            'chunk_count' => 0,
            'ingested_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => DocumentStatus::Failed,
            'error_message' => 'Failed to parse document.',
            'chunk_count' => 0,
            'ingested_at' => null,
        ]);
    }

    public function fromDirectory(): static
    {
        return $this->state(fn () => [
            'source' => DocumentSource::Directory,
        ]);
    }

    public function fromStorageDisk(string $disk = 's3'): static
    {
        return $this->state(fn () => [
            'source' => DocumentSource::StorageDisk,
            'storage_disk' => $disk,
            'file_path' => 'documents/'.fake()->word().'.pdf',
        ]);
    }
}
