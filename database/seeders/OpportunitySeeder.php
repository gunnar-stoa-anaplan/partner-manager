<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;

class OpportunitySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating opportunities...');

        $partners = Partner::all();
        $admin = User::where('role', 'admin')->first();

        // Create a dummy import record for seeded data
        $import = Import::create([
            'type' => 'opportunities',
            'file_name' => 'seed_data.xlsx',
            'file_path' => 'seed_data.xlsx',
            'status' => 'completed',
            'user_id' => $admin->id ?? 1,
            'completed_at' => now(),
        ]);

        $stages = ['Prospecting', 'Qualification', 'Needs Analysis', 'Value Proposition', 'Proposal/Price Quote', 'Negotiation/Review', 'Closed Won', 'Closed Lost'];
        $revenueTypes = ['sourced', 'influenced'];
        $productAreas = ['IFP', 'SCM', 'SPM', 'OWP', 'Polaris', 'Forecaster', 'ADO'];
        $owners = ['Sarah Mitchell', 'James Chen', 'Priya Sharma', 'Michael O\'Brien', 'Anna Kowalski', 'David Thompson'];

        $count = 0;
        foreach ($partners as $partner) {
            // Each partner gets 8-20 opportunities across various stages and time periods
            $oppCount = fake()->numberBetween(8, 20);

            for ($i = 0; $i < $oppCount; $i++) {
                // Weighted stage distribution — funnel shape
                $stage = fake()->randomElement(array_merge(
                    array_fill(0, 6, 'Prospecting'),
                    array_fill(0, 5, 'Qualification'),
                    array_fill(0, 4, 'Needs Analysis'),
                    array_fill(0, 3, 'Value Proposition'),
                    array_fill(0, 2, 'Proposal/Price Quote'),
                    array_fill(0, 2, 'Negotiation/Review'),
                    array_fill(0, 3, 'Closed Won'),
                    array_fill(0, 2, 'Closed Lost'),
                ));

                $isClosed = in_array($stage, ['Closed Won', 'Closed Lost']);
                $closeDate = $isClosed
                    ? fake()->dateTimeBetween('-12 months', 'now')
                    : fake()->dateTimeBetween('now', '+9 months');

                $closeDateStr = $closeDate->format('Y-m-d');
                $month = (int) $closeDate->format('m');
                $year = (int) $closeDate->format('Y');

                // Anaplan fiscal year starts in Feb (month 2)
                $fyStartMonth = 2;
                $fyYear = $month >= $fyStartMonth ? $year + 1 : $year;
                $fiscalYear = $fyYear;

                // Determine fiscal quarter
                $adjustedMonth = (($month - $fyStartMonth + 12) % 12) + 1;
                $fiscalQtr = ceil($adjustedMonth / 3);
                $fiscalQuarter = "Q{$fiscalQtr}";

                $amount = match (true) {
                    $stage === 'Prospecting' => fake()->randomFloat(2, 25000, 150000),
                    in_array($stage, ['Qualification', 'Needs Analysis']) => fake()->randomFloat(2, 50000, 300000),
                    in_array($stage, ['Value Proposition', 'Proposal/Price Quote']) => fake()->randomFloat(2, 75000, 500000),
                    $stage === 'Negotiation/Review' => fake()->randomFloat(2, 100000, 750000),
                    $stage === 'Closed Won' => fake()->randomFloat(2, 50000, 600000),
                    default => fake()->randomFloat(2, 30000, 200000),
                };

                $probabilityMap = [
                    'Prospecting' => 10, 'Qualification' => 20, 'Needs Analysis' => 30,
                    'Value Proposition' => 50, 'Proposal/Price Quote' => 65,
                    'Negotiation/Review' => 80, 'Closed Won' => 100, 'Closed Lost' => 0,
                ];

                Opportunity::create([
                    'salesforce_id' => 'OPP-'.fake()->unique()->numerify('######'),
                    'opportunity_name' => fake()->company().' - '.fake()->randomElement(['Implementation', 'Expansion', 'Migration', 'Platform Deployment', 'Optimization']),
                    'account_name' => fake()->company(),
                    'partner_id' => $partner->id,
                    'stage' => $stage,
                    'amount' => $amount,
                    'close_date' => $closeDateStr,
                    'fiscal_quarter' => $fiscalQuarter,
                    'fiscal_year' => $fiscalYear,
                    'type' => fake()->randomElement(['New Business', 'Existing Customer - Upgrade', 'Existing Customer - Replacement']),
                    'description' => fake()->sentence(),
                    'lead_source' => fake()->randomElement(['Partner Referral', 'Web', 'Event', 'Cold Call', 'LinkedIn']),
                    'product_area' => fake()->randomElement($productAreas),
                    'probability' => $probabilityMap[$stage] ?? 50,
                    'owner' => fake()->randomElement($owners),
                    'revenue_type' => fake()->randomElement($revenueTypes),
                    'origin' => fake()->randomElement(['partner_sourced', 'anaplan_sourced', 'joint']),
                    'import_id' => $import->id,
                ]);

                $count++;
            }
        }

        // Update import stats
        $import->update([
            'total_rows' => $count,
            'successful_rows' => $count,
            'opportunities_created' => $count,
        ]);

        $this->command->info("Created {$count} opportunities across ".$partners->count().' partners.');
    }
}
