<?php

namespace Database\Factories;

use App\Models\ResourcePath;
use App\Models\ResourcePathStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourcePathStageFactory extends Factory
{
    protected $model = ResourcePathStage::class;

    public function definition(): array
    {
        return [
            'resource_path_id' => ResourcePath::factory(),
            'stage_number' => 1,
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'color' => $this->faker->hexColor(),
            'sort_order' => 1,
        ];
    }
}
