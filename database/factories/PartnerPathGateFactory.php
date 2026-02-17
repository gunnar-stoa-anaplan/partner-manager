<?php

namespace Database\Factories;

use App\Models\PartnerPath;
use App\Models\PartnerPathGate;
use App\Models\PartnerPathStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerPathGate>
 */
class PartnerPathGateFactory extends Factory
{
    protected $model = PartnerPathGate::class;

    public function definition(): array
    {
        return [
            'partner_path_id' => PartnerPath::factory(),
            'from_stage_id' => PartnerPathStage::factory(),
            'to_stage_id' => PartnerPathStage::factory(),
            'requirement_key' => 'contact_count',
            'requirement_label' => 'Minimum Contacts',
            'comparison' => 'gte',
            'threshold_value' => 3,
            'unit' => 'count',
            'sort_order' => 0,
        ];
    }
}
