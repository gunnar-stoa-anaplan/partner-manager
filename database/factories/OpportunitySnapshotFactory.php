<?php

namespace Database\Factories;

use App\Models\OpportunitySnapshot;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunitySnapshotFactory extends Factory
{
    protected $model = OpportunitySnapshot::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'snapshot_date' => now()->subDays($this->faker->numberBetween(0, 90)),
            'opportunity_name' => $this->faker->catchPhrase(),
            'account_name' => $this->faker->company(),
            'stage' => $this->faker->randomElement(['Prospecting', 'Qualification', 'Proposal', 'Closed Won', 'Closed Lost']),
            'amount' => $this->faker->randomFloat(2, 10000, 1000000),
            'close_date' => now()->addDays($this->faker->numberBetween(-30, 120)),
        ];
    }
}
