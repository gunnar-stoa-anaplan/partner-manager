<?php

namespace Database\Factories;

use App\Models\AnaplanWorkspace;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnaplanWorkspace>
 */
class AnaplanWorkspaceFactory extends Factory
{
    protected $model = AnaplanWorkspace::class;

    public function definition(): array
    {
        $sizeAllowance = $this->faker->randomElement([
            5_368_709_120,   // 5 GB
            10_737_418_240,  // 10 GB
            21_474_836_480,  // 20 GB
            53_687_091_200,  // 50 GB
        ]);

        return [
            'anaplan_id' => $this->faker->uuid(),
            'name' => $this->faker->company().' Workspace',
            'partner_id' => Partner::factory(),
            'active' => true,
            'size_allowance' => $sizeAllowance,
            'current_size' => (int) ($sizeAllowance * $this->faker->randomFloat(2, 0.1, 0.85)),
            'last_synced_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
        ];
    }

    public function unassigned(): static
    {
        return $this->state(fn () => [
            'partner_id' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }
}
