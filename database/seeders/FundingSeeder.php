<?php

namespace Database\Seeders;

use App\Models\Funding;
use App\Models\Partner;
use Illuminate\Database\Seeder;

class FundingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating funding records...');

        $partners = Partner::all();
        $count = 0;

        $fundingTypes = [
            'MDF' => ['Market Development Fund - {purpose}', 10000, 75000],
            'SPF' => ['Strategic Partner Fund - {purpose}', 25000, 150000],
            'Co-Marketing' => ['Co-Marketing Campaign: {purpose}', 5000, 50000],
            'Training' => ['Training Investment - {purpose}', 3000, 25000],
            'Demo' => ['Demo Environment Fund - {purpose}', 2000, 15000],
        ];

        $purposes = [
            'MDF' => ['Event Sponsorship', 'Digital Campaign', 'Webinar Series', 'Trade Show Booth', 'Content Development'],
            'SPF' => ['Practice Build', 'Solution Development', 'GTM Strategy', 'Team Expansion', 'Certification Program'],
            'Co-Marketing' => ['Joint Webinar', 'Case Study', 'White Paper', 'LinkedIn Campaign', 'Customer Event'],
            'Training' => ['MB Certification', 'SA Certification', 'App Academy', 'Bootcamp', 'Workshop'],
            'Demo' => ['Customer Demo Env', 'POC Environment', 'Training Sandbox', 'Solution Showcase'],
        ];

        foreach ($partners as $partner) {
            $fundCount = fake()->numberBetween(2, 6);

            for ($i = 0; $i < $fundCount; $i++) {
                $type = fake()->randomElement(array_keys($fundingTypes));
                [$nameTemplate, $minAmount, $maxAmount] = $fundingTypes[$type];
                $purpose = fake()->randomElement($purposes[$type]);
                $name = str_replace('{purpose}', $purpose, $nameTemplate);

                $date = fake()->dateTimeBetween('-12 months', '+3 months');
                $isPast = $date < now();

                $status = $isPast
                    ? fake()->randomElement(['Approved', 'Approved', 'Disbursed', 'Rejected'])
                    : fake()->randomElement(['Pending', 'Approved', 'Pending']);

                Funding::create([
                    'partner_id' => $partner->id,
                    'name' => $name,
                    'amount' => fake()->randomFloat(2, $minAmount, $maxAmount),
                    'status' => $status,
                    'type' => $type,
                    'description' => "Funding for {$purpose} activities with {$partner->name}.",
                    'date' => $date,
                ]);

                $count++;
            }
        }

        $this->command->info("Created {$count} funding records.");
    }
}
