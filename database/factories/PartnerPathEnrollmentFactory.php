<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerPathStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerPathEnrollmentFactory extends Factory
{
    protected $model = PartnerPathEnrollment::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'partner_path_id' => PartnerPath::factory(),
            'current_stage_id' => PartnerPathStage::factory(),
            'enrolled_at' => now()->subDays(30),
            'stage_entered_at' => now()->subDays(15),
            'status' => 'active',
        ];
    }
}
