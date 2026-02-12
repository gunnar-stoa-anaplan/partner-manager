<?php

namespace Database\Factories;

use App\Models\Import;
use App\Models\Opportunity;
use App\Models\OpportunitySnapshot;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunitySnapshotFactory extends Factory
{
    protected $model = OpportunitySnapshot::class;

    public function definition(): array
    {
        $closeDate = $this->faker->dateTimeBetween('now', '+6 months');

        return [
            'opportunity_id' => Opportunity::factory(),
            'import_id' => Import::factory(),
            'snapshot_date' => now(),
            'opportunity_name' => $this->faker->company() . ' - Implementation',
            'account_name' => $this->faker->company(),
            'partner_id' => Partner::factory(),
            'stage' => $this->faker->randomElement([
                'Prospecting',
                'Qualification',
                'Needs Analysis',
                'Value Proposition',
                'Proposal/Price Quote',
                'Negotiation/Review',
                'Closed Won',
            ]),
            'amount' => $this->faker->randomFloat(2, 10000, 500000),
            'close_date' => $closeDate,
            'fiscal_quarter' => 'Q' . $this->faker->numberBetween(1, 4),
            'fiscal_year' => $this->faker->numberBetween(2024, 2026),
            'type' => $this->faker->randomElement(['New Business', 'Existing Customer - Upgrade']),
            'lead_source' => $this->faker->randomElement(['Partner Referral', 'Web', 'Event']),
            'revenue_type' => $this->faker->randomElement(['sourced', 'influenced', 'influenced', 'influenced']),
            'product_area' => $this->faker->randomElement(['Finance', 'Supply Chain', 'Sales', 'Workforce', 'Platform']),
            'probability' => $this->faker->randomElement([10, 25, 50, 75, 90]),
            'owner' => $this->faker->name(),
        ];
    }

    /**
     * Create snapshot from an opportunity.
     */
    public function fromOpportunity(Opportunity $opportunity, $snapshotDate): static
    {
        return $this->state(fn (array $attributes) => [
            'opportunity_id' => $opportunity->id,
            'snapshot_date' => $snapshotDate,
            'opportunity_name' => $opportunity->opportunity_name,
            'account_name' => $opportunity->account_name,
            'partner_id' => $opportunity->partner_id,
            'stage' => $opportunity->stage,
            'amount' => $opportunity->amount,
            'close_date' => $opportunity->close_date,
            'fiscal_quarter' => $opportunity->fiscal_quarter,
            'fiscal_year' => $opportunity->fiscal_year,
            'type' => $opportunity->type,
            'lead_source' => $opportunity->lead_source,
            'revenue_type' => $opportunity->revenue_type ?? 'influenced',
            'product_area' => $opportunity->product_area,
            'probability' => $opportunity->probability,
            'owner' => $opportunity->owner,
        ]);
    }
}
