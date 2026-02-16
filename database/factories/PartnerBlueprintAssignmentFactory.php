<?php

namespace Database\Factories;

use App\Models\Blueprint;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerBlueprintAssignment>
 */
class PartnerBlueprintAssignmentFactory extends Factory
{
    protected $model = PartnerBlueprintAssignment::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'blueprint_id' => Blueprint::factory(),
            'role' => $this->faker->randomElement(array_keys(PartnerBlueprintAssignment::ROLES)),
            'assigned_by' => User::factory(),
            'status' => 'active',
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function gtm(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'gtm',
        ]);
    }

    public function preSales(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pre_sales',
        ]);
    }

    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'delivery',
        ]);
    }
}
