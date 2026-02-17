<?php

namespace Database\Factories;

use App\Models\PartnerPath;
use App\Models\PartnerPathStage;
use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskTemplate>
 */
class TaskTemplateFactory extends Factory
{
    protected $model = TaskTemplate::class;

    public function definition(): array
    {
        return [
            'partner_path_id' => PartnerPath::factory(),
            'stage_id' => PartnerPathStage::factory(),
            'category' => fake()->randomElement(array_keys(TaskTemplate::CATEGORIES)),
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
