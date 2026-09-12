<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\ResourceSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceSnapshotFactory extends Factory
{
    protected $model = ResourceSnapshot::class;

    public function definition(): array
    {
        $total = $this->faker->numberBetween(1, 50);
        $completed = $this->faker->numberBetween(0, $total);

        return [
            'partner_id' => Partner::factory(),
            'snapshot_date' => now()->subDays($this->faker->numberBetween(0, 90)),
            'program' => $this->faker->randomElement(['IFP', 'Delivery Excellence', 'Sales Bootcamp']),
            'role' => $this->faker->randomElement(['Delivery', 'Sales', 'Pre-Sales']),
            'started_count' => $completed,
            'completed_count' => $completed,
            'total_count' => $total,
            'unique_people_count' => $this->faker->numberBetween(1, $total),
        ];
    }
}
