<?php

namespace Database\Factories;

use App\Models\ResourcePath;
use App\Models\ResourcePathStage;
use App\Models\ResourceTaskTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceTaskTemplateFactory extends Factory
{
    protected $model = ResourceTaskTemplate::class;

    public function definition(): array
    {
        return [
            'resource_path_id' => ResourcePath::factory(),
            'stage_id' => ResourcePathStage::factory(),
            'category' => fake()->randomElement(array_keys(ResourceTaskTemplate::CATEGORIES)),
            'requirement_level' => 'required',
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'effort_size' => 'medium',
            'default_owner' => 'psm',
            'evidence_type' => 'psm_attestation',
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
