<?php

namespace Database\Factories;

use App\Models\Import;
use App\Models\Partner;
use App\Models\ResourceSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceSnapshotFactory extends Factory
{
    protected $model = ResourceSnapshot::class;

    public function definition(): array
    {
        $startedCount = $this->faker->numberBetween(0, 10);
        $completedCount = $this->faker->numberBetween(0, 15);

        return [
            'partner_id' => Partner::factory(),
            'import_id' => Import::factory(),
            'snapshot_date' => now(),
            'program' => $this->faker->randomElement(['IFP', 'I&D', 'SPM', 'OWP']),
            'role' => $this->faker->randomElement(['Delivery', 'Pre-Sales', 'Sales']),
            'started_count' => $startedCount,
            'completed_count' => $completedCount,
            'total_count' => $startedCount + $completedCount,
            'unique_people_count' => $this->faker->numberBetween(1, $startedCount + $completedCount),
        ];
    }

    /**
     * Set specific counts.
     */
    public function withCounts(int $started, int $completed, int $uniquePeople): static
    {
        return $this->state(fn (array $attributes) => [
            'started_count' => $started,
            'completed_count' => $completed,
            'total_count' => $started + $completed,
            'unique_people_count' => $uniquePeople,
        ]);
    }
}
