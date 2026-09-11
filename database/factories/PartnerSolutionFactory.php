<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerSolution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerSolution>
 */
class PartnerSolutionFactory extends Factory
{
    protected $model = PartnerSolution::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'name' => fake()->unique()->catchPhrase(),
            'product_area' => fake()->randomElement(['Planning', 'Data', 'Integration', 'Reporting']),
            'lifecycle_stage' => 'build',
            'stage_entered_at' => now()->subDays(fake()->numberBetween(1, 90)),
            'status' => 'active',
            'created_by' => User::factory(),
        ];
    }
}
