<?php

namespace Database\Factories;

use App\Helpers\FiscalYearHelper;
use App\Models\Import;
use App\Models\Opportunity;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        $closeDate = $this->faker->dateTimeBetween('now', '+6 months');
        $fiscalPeriod = FiscalYearHelper::calculateFiscalPeriod($closeDate->format('Y-m-d'));

        return [
            'salesforce_id' => 'OPP-' . $this->faker->unique()->numerify('######'),
            'opportunity_name' => $this->faker->randomElement([
                $this->faker->company() . ' - Implementation',
                $this->faker->company() . ' - Expansion',
                $this->faker->company() . ' - Migration',
                $this->faker->company() . ' - Consulting',
                $this->faker->company() . ' - Support',
            ]),
            'account_name' => $this->faker->company(),
            'partner_id' => Partner::factory(),
            'stage' => $this->faker->randomElement(
                // Weighted toward early stages to produce a funnel distribution
                array_merge(
                    array_fill(0, 8, 'Prospecting'),
                    array_fill(0, 6, 'Qualification'),
                    array_fill(0, 4, 'Needs Analysis'),
                    array_fill(0, 3, 'Value Proposition'),
                    array_fill(0, 2, 'Proposal/Price Quote'),
                    array_fill(0, 1, 'Negotiation/Review'),
                    array_fill(0, 1, 'Closed Won'),
                )
            ),
            'amount' => $this->faker->randomFloat(2, 10000, 500000),
            'close_date' => $closeDate,
            'fiscal_quarter' => $fiscalPeriod['fiscal_quarter'],
            'fiscal_year' => $fiscalPeriod['fiscal_year'],
            'type' => $this->faker->randomElement(['New Business', 'Existing Customer - Upgrade', 'Existing Customer - Replacement']),
            'description' => $this->faker->sentence(),
            'lead_source' => $this->faker->randomElement(['Partner Referral', 'Web', 'Event', 'Cold Call', 'LinkedIn']),
            'product_area' => $this->faker->randomElement(\App\Models\Blueprint::PRODUCT_AREAS),
            'probability' => $this->faker->randomElement([10, 25, 50, 75, 90, 100]),
            'owner' => $this->faker->name(),
            'revenue_type' => $this->faker->randomElement(['sourced', 'influenced', 'influenced', 'influenced']),
            'origin' => $this->faker->randomElement(['partner_sourced', 'anaplan_sourced', 'joint']),
            'excel_data' => [],
            'import_id' => Import::factory(),
        ];
    }

    /**
     * Set a specific stage for the opportunity.
     */
    public function stage(string $stage): static
    {
        $probabilityMap = [
            'Prospecting' => 10,
            'Qualification' => 20,
            'Needs Analysis' => 30,
            'Value Proposition' => 40,
            'Proposal/Price Quote' => 60,
            'Negotiation/Review' => 80,
            'Closed Won' => 100,
            'Closed Lost' => 0,
        ];

        return $this->state(fn (array $attributes) => [
            'stage' => $stage,
            'probability' => $probabilityMap[$stage] ?? 50,
        ]);
    }

    /**
     * Set a specific partner for the opportunity.
     */
    public function forPartner($partnerId): static
    {
        return $this->state(fn (array $attributes) => [
            'partner_id' => $partnerId,
        ]);
    }

    /**
     * Set a specific import for the opportunity.
     */
    public function forImport($importId): static
    {
        return $this->state(fn (array $attributes) => [
            'import_id' => $importId,
        ]);
    }
}
