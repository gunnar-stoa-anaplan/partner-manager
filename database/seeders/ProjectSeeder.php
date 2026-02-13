<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating projects from closed-won opportunities...');

        $partners = Partner::with(['accounts', 'contacts'])->get();
        $projectCount = 0;

        foreach ($partners as $partner) {
            $closedWonOpps = Opportunity::where('partner_id', $partner->id)
                ->where('stage', 'Closed Won')
                ->get();

            if ($closedWonOpps->isEmpty()) {
                continue;
            }

            // Ensure partner has accounts
            if ($partner->accounts->isEmpty()) {
                $accounts = Account::inRandomOrder()->take(rand(1, 3))->get();
                if ($accounts->isNotEmpty()) {
                    $partner->accounts()->attach($accounts->pluck('id'));
                    $partner->load('accounts');
                }
            }

            // Ensure partner has contacts
            if ($partner->contacts->isEmpty()) {
                Contact::factory()->count(rand(2, 5))->create([
                    'partner_id' => $partner->id,
                ]);
                $partner->load('contacts');
            }

            $partnerAccounts = $partner->accounts->pluck('id');
            $contacts = $partner->contacts->pluck('id')->all();

            foreach ($closedWonOpps as $opp) {
                $accountId = $partnerAccounts->isNotEmpty()
                    ? $partnerAccounts->random()
                    : (Account::inRandomOrder()->first()?->id ?? Account::factory()->create()->id);

                $startDate = $opp->close_date?->copy()->addDays(rand(7, 30));
                $status = fake()->randomElement(['Draft', 'In Progress', 'In Progress', 'Completed', 'On Hold']);
                $percentComplete = match ($status) {
                    'Completed' => 100,
                    'Draft' => 0,
                    default => fake()->numberBetween(10, 90),
                };

                // Weighted phase: 20% kickoff, 50% implementation, 30% live
                $phase = fake()->randomElement(array_merge(
                    array_fill(0, 2, 'kickoff'),
                    array_fill(0, 5, 'implementation'),
                    array_fill(0, 3, 'live'),
                ));

                $project = Project::create([
                    'partner_id' => $partner->id,
                    'account_id' => $accountId,
                    'opportunity_id' => $opp->id,
                    'name' => $opp->opportunity_name,
                    'start_date' => $startDate,
                    'cal_id' => $contacts[0] ?? null,
                    'tal_id' => $contacts[1] ?? null,
                    'ae_id' => $contacts[2] ?? null,
                    'acv' => $opp->amount,
                    'status' => $status,
                    'phase' => $phase,
                    'percent_complete' => $percentComplete,
                    'total_sprints' => fake()->numberBetween(3, 12),
                    'current_sprint' => fake()->numberBetween(1, 8),
                    'delivery_team' => json_encode(fake()->words(rand(2, 5))),
                ]);

                if (! empty($contacts)) {
                    $project->contacts()->sync(
                        collect($contacts)->random(min(count($contacts), rand(2, 5)))->all()
                    );
                }

                $projectCount++;
            }
        }

        $this->command->info("Created {$projectCount} projects from closed-won opportunities.");
    }
}
