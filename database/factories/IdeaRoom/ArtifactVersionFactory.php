<?php

namespace Database\Factories\IdeaRoom;

use App\Models\IdeaRoom\Artifact;
use App\Models\IdeaRoom\ArtifactVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtifactVersion>
 */
class ArtifactVersionFactory extends Factory
{
    protected $model = ArtifactVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'artifact_id' => Artifact::factory(),
            'version' => 1,
            'content' => fake()->paragraphs(2, true),
            'file_path' => null,
            'changed_by_type' => 'user',
            'changed_by_id' => null,
        ];
    }
}
