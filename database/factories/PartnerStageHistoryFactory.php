<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathStage;
use App\Models\PartnerStageHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerStageHistoryFactory extends Factory
{
    protected $model = PartnerStageHistory::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'partner_path_id' => PartnerPath::factory(),
            'from_stage_id' => null,
            'to_stage_id' => PartnerPathStage::factory(),
            'transitioned_at' => now()->subDays(10),
            'approved_by' => User::factory(),
        ];
    }
}
