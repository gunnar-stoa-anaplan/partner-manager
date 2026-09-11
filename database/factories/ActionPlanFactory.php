<?php

namespace Database\Factories;

use App\Models\ActionPlan;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActionPlan>
 */
class ActionPlanFactory extends Factory
{
    protected $model = ActionPlan::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'title' => fake()->sentence(4),
            'source' => 'psm_initiative',
            'status' => 'open',
            'priority' => 'medium',
            'owner_type' => 'psm',
            'due_date' => now()->addDays(fake()->numberBetween(7, 60))->toDateString(),
            'created_by' => User::factory(),
        ];
    }
}
