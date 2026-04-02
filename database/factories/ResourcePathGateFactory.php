<?php

namespace Database\Factories;

use App\Models\ResourcePath;
use App\Models\ResourcePathGate;
use App\Models\ResourcePathStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourcePathGateFactory extends Factory
{
    protected $model = ResourcePathGate::class;

    public function definition(): array
    {
        return [
            'resource_path_id' => ResourcePath::factory(),
            'from_stage_id' => ResourcePathStage::factory(),
            'to_stage_id' => ResourcePathStage::factory(),
            'requirement_key' => 'active_certification_count',
            'requirement_label' => 'Active Certifications',
            'comparison' => 'gte',
            'threshold_value' => 1,
            'unit' => 'count',
        ];
    }
}
