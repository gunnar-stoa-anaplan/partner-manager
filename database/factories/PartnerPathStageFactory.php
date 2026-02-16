<?php

namespace Database\Factories;

use App\Models\PartnerPath;
use App\Models\PartnerPathStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerPathStageFactory extends Factory
{
    protected $model = PartnerPathStage::class;

    public function definition(): array
    {
        return [
            'partner_path_id' => PartnerPath::factory(),
            'stage_number' => 1,
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'color' => $this->faker->hexColor(),
            'sort_order' => 1,
        ];
    }
}
