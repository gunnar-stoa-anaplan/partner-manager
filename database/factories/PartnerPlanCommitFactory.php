<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerPlanCommit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerPlanCommit>
 */
class PartnerPlanCommitFactory extends Factory
{
    protected $model = PartnerPlanCommit::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'lob' => '',
            'role' => fake()->unique()->jobTitle(),
            'committed' => fake()->numberBetween(1, 10),
        ];
    }
}
