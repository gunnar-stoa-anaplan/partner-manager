<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Partner;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating projects for partners...');

        // Check if accounts exist, if not create some
        if (Account::count() === 0) {
            $this->command->warn('No accounts found. Creating sample accounts...');
            Account::factory()->count(10)->create();
        }

        // 1) Projects for the Accenture partner
        $accenture = Partner::where('name', 'Accenture')->first();

        if ($accenture) {
            // Ensure Accenture has accounts
            if ($accenture->accounts()->count() === 0) {
                $accounts = Account::inRandomOrder()->take(5)->get();
                $accenture->accounts()->attach($accounts->pluck('id'));
            }

            // Ensure Accenture has contacts
            if ($accenture->contacts()->count() === 0) {
                $this->command->info('Creating contacts for Accenture...');
                Contact::factory()->count(10)->create([
                    'partner_id' => $accenture->id,
                ]);
            }

            $accounts = $accenture->accounts()->inRandomOrder()->take(5)->pluck('accounts.id')->all();

            if (!empty($accounts)) {
                foreach (range(1, 15) as $i) {
                    $accountId = $accounts[$i % count($accounts)];
                    $contacts = $accenture->contacts()->inRandomOrder()->take(10)->pluck('id')->all();

                    $project = Project::create([
                        'partner_id'       => $accenture->id,
                        'account_id'       => $accountId,
                        'name'             => fake()->catchPhrase() . ' Implementation',
                        'start_date'       => now()->subDays(rand(0, 365)),
                        'cal_id'           => $contacts[0] ?? null,
                        'tal_id'           => $contacts[1] ?? null,
                        'ae_id'            => $contacts[2] ?? null,
                        'acv'              => fake()->randomFloat(2, 50000, 500000),
                        'status'           => fake()->randomElement(['Draft', 'In Progress', 'Completed', 'On Hold']),
                        'percent_complete' => fake()->numberBetween(0, 100),
                        'total_sprints'    => fake()->numberBetween(4, 12),
                        'current_sprint'   => fake()->numberBetween(1, 12),
                        'delivery_team'    => json_encode(fake()->words(rand(3, 6))),
                    ]);

                    if (!empty($contacts)) {
                        $project->contacts()->sync($contacts);
                    }
                }

                $this->command->info("✓ Created 15 projects for Accenture");
            }
        }

        // 2) Projects for all other partners
        $otherPartners = Partner::where('name', '!=', 'Accenture')->get();
        $projectCount = 0;

        foreach ($otherPartners as $partner) {
            // Ensure partner has accounts
            if ($partner->accounts()->count() === 0) {
                $accounts = Account::inRandomOrder()->take(rand(1, 3))->get();
                if ($accounts->isNotEmpty()) {
                    $partner->accounts()->attach($accounts->pluck('id'));
                }
            }

            // Ensure partner has contacts
            if ($partner->contacts()->count() === 0) {
                Contact::factory()->count(rand(2, 5))->create([
                    'partner_id' => $partner->id,
                ]);
            }

            $partnerAccounts = $partner->accounts()->pluck('accounts.id');

            if ($partnerAccounts->isNotEmpty()) {
                foreach (range(1, rand(1, 3)) as $i) {
                    $accountId = $partnerAccounts->random();
                    $contacts = $partner->contacts()->inRandomOrder()->take(5)->pluck('id')->all();

                    $project = Project::create([
                        'partner_id'       => $partner->id,
                        'account_id'       => $accountId,
                        'name'             => fake()->catchPhrase() . ' Project',
                        'start_date'       => now()->subDays(rand(0, 365)),
                        'cal_id'           => $contacts[0] ?? null,
                        'tal_id'           => $contacts[1] ?? null,
                        'ae_id'            => $contacts[2] ?? null,
                        'acv'              => fake()->randomFloat(2, 10000, 300000),
                        'status'           => fake()->randomElement(['Draft', 'In Progress', 'Completed']),
                        'percent_complete' => fake()->numberBetween(0, 100),
                        'total_sprints'    => fake()->numberBetween(2, 8),
                        'current_sprint'   => fake()->numberBetween(1, 8),
                        'delivery_team'    => json_encode(fake()->words(rand(2, 4))),
                    ]);

                    if (!empty($contacts)) {
                        $project->contacts()->sync($contacts);
                    }

                    $projectCount++;
                }
            }
        }

        $this->command->info("✓ Created {$projectCount} projects for other partners");
        $this->command->info("Total projects: " . Project::count());
    }
}