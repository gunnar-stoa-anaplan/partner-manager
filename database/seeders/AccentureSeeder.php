<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Funding;
use App\Models\Lead;
use App\Models\Partner;
use App\Models\Project;

class AccentureSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Enriching Accenture partner data...');

        $accenture = Partner::where('name', 'Accenture')->first();

        if (!$accenture) {
            $this->command->warn('Accenture partner not found. Skipping AccentureSeeder.');
            return;
        }

        // 1. Ensure Accenture has accounts
        if ($accenture->accounts()->count() === 0) {
            $this->command->info('Creating accounts for Accenture...');

            // Create some accounts if they don't exist
            if (Account::count() === 0) {
                Account::factory()->count(10)->create();
            }

            $accounts = Account::inRandomOrder()->take(5)->get();
            $accenture->accounts()->attach($accounts->pluck('id'));
        }

        // 2. Create industry-specific contacts for Accenture
        $titles = [
            'Client Delivery Lead',
            'Solutions Architect',
            'Engagement Manager',
            'Technology Consultant',
            'Business Analyst',
            'Strategy Director',
            'Industry Principal',
            'Technical Account Manager',
            'Partner Alliance Manager',
            'Client Account Lead',
            'Practice Lead',
            'Managing Director',
            'Senior Manager',
            'Platform Architect',
        ];

        $specificContacts = [
            ['first_name' => 'Alice', 'last_name' => 'Johnson', 'title' => 'Client Account Lead', 'email' => 'alice.johnson@accenture.com'],
            ['first_name' => 'Bob', 'last_name' => 'Smith', 'title' => 'Technical Account Manager', 'email' => 'bob.smith@accenture.com'],
            ['first_name' => 'Carol', 'last_name' => 'White', 'title' => 'Managing Director', 'email' => 'carol.white@accenture.com'],
            ['first_name' => 'David', 'last_name' => 'Chen', 'title' => 'Solutions Architect', 'email' => 'david.chen@accenture.com'],
            ['first_name' => 'Emma', 'last_name' => 'Rodriguez', 'title' => 'Engagement Manager', 'email' => 'emma.rodriguez@accenture.com'],
        ];

        foreach ($specificContacts as $contactData) {
            Contact::updateOrCreate(
                [
                    'email' => $contactData['email'],
                ],
                [
                    'partner_id' => $accenture->id,
                    'first_name' => $contactData['first_name'],
                    'last_name' => $contactData['last_name'],
                    'phone' => fake()->phoneNumber(),
                    'title' => $contactData['title'],
                    'avatar' => 'img/demo/avatars/avatar-' . fake()->numberBetween(1, 18) . '.png',
                ]
            );
        }

        // Add more contacts with partner-appropriate emails (factory now auto-generates accenture.com emails)
        Contact::factory()->count(10)->create([
            'partner_id' => $accenture->id,
            'title' => fake()->randomElement($titles),
        ]);

        $this->command->info("✓ Created " . $accenture->contacts()->count() . " contacts for Accenture");

        // 3. Seed Leads assigned to Accenture
        if (Lead::where('partner_id', $accenture->id)->count() === 0) {
            Lead::factory()->count(15)->create([
                'partner_id' => $accenture->id,
                'status' => fake()->randomElement(['unassigned', 'assigned', 'accepted', 'rejected']),
            ]);

            $this->command->info("✓ Created 15 leads for Accenture");
        }

        // 4. Seed Funding records for Accenture (if factory exists)
        if (class_exists('Database\Factories\FundingFactory')) {
            if (Funding::where('partner_id', $accenture->id)->count() === 0) {
                Funding::factory()->count(5)->create([
                    'partner_id' => $accenture->id,
                    'status' => fake()->randomElement(['Pending', 'Approved', 'Disbursed', 'Rejected']),
                ]);

                $this->command->info("✓ Created 5 funding records for Accenture");
            }
        }

        $this->command->info('✅ Accenture enrichment complete!');
        $this->command->info("   Contacts: " . $accenture->contacts()->count());
        $this->command->info("   Accounts: " . $accenture->accounts()->count());
        $this->command->info("   Leads: " . Lead::where('partner_id', $accenture->id)->count());
        $this->command->info("   Projects: " . $accenture->projects()->count());
        $this->command->info("   Opportunities: " . $accenture->opportunities()->count());
        $this->command->info("   Resources: " . $accenture->resources()->count());
    }
}