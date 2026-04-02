<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\ResourcePath;
use App\Models\ResourcePathStage;
use App\Models\ResourceStageHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceStageHistoryFactory extends Factory
{
    protected $model = ResourceStageHistory::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'partner_id' => Partner::factory(),
            'resource_path_id' => ResourcePath::factory(),
            'from_stage_id' => null,
            'to_stage_id' => ResourcePathStage::factory(),
            'transitioned_at' => now()->subDays(10),
            'approved_by' => User::factory(),
        ];
    }
}
