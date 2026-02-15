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
    /**
     * Realistic project names by product area.
     */
    private array $projectNameTemplates = [
        'Finance' => [
            '{account} Financial Close Optimization',
            '{account} Consolidation & Reporting',
            '{account} Integrated Financial Planning',
            '{account} Disclosure Management Rollout',
            '{account} FP&A Transformation',
            '{account} Financial Data Integration',
        ],
        'Supply Chain' => [
            '{account} Demand Planning Implementation',
            '{account} S&OP Transformation',
            '{account} Inventory Optimization',
            '{account} Supply Planning Deployment',
            '{account} IBP Rollout',
            '{account} Production Planning Modernization',
        ],
        'Sales' => [
            '{account} Territory & Quota Planning',
            '{account} Revenue Performance Management',
            '{account} GTM Capacity Planning',
            '{account} Sales Segmentation & Scoring',
        ],
        'Workforce' => [
            '{account} Workforce Planning Deployment',
            '{account} Operational Headcount Modeling',
            '{account} Workforce Analytics Rollout',
        ],
        'Platform' => [
            '{account} Polaris Migration',
            '{account} Platform Upgrade',
            '{account} Anaplan Data Hub Implementation',
        ],
    ];

    /**
     * Partner-specific project profiles.
     * [extra_completed, extra_in_progress, extra_planning, satisfaction_min, satisfaction_max]
     */
    private function getPartnerProfile(string $partnerName): array
    {
        return match (true) {
            str_contains($partnerName, 'Accenture') => [6, 5, 3, 3, 5],
            str_contains($partnerName, 'Deloitte') => [5, 4, 2, 3, 5],
            str_contains($partnerName, 'PwC') => [4, 3, 2, 3, 5],
            default => [2, 2, 1, 2, 5],
        };
    }

    public function run(): void
    {
        $this->command->info('Creating projects from closed-won opportunities + planned projects from pipeline...');

        $partners = Partner::with(['accounts', 'contacts'])->get();
        $projectCount = 0;

        foreach ($partners as $partner) {
            $this->ensurePartnerHasAccountsAndContacts($partner);
            $partnerAccounts = $partner->accounts->pluck('id');
            $contacts = $partner->contacts->pluck('id')->all();

            [$extraCompleted, $extraInProgress, $extraPlanning, $satMin, $satMax] = $this->getPartnerProfile($partner->name);

            // ── 1. Projects from Closed Won opportunities ──
            $closedWonOpps = Opportunity::where('partner_id', $partner->id)
                ->where('stage', 'Closed Won')
                ->get();

            foreach ($closedWonOpps as $opp) {
                $status = fake()->randomElement(['In Progress', 'In Progress', 'In Progress', 'Completed', 'Completed']);
                $project = $this->createProject($partner, $opp, $status, $partnerAccounts, $contacts, $satMin, $satMax);
                $projectCount++;
            }

            // ── 2. Extra completed projects (historical delivery track record) ──
            $this->createExtraProjects(
                $partner, $extraCompleted, 'Completed', $partnerAccounts, $contacts, $satMin, $satMax, $projectCount
            );
            $projectCount += $extraCompleted;

            // ── 3. Extra in-progress projects from mid-pipeline opps ──
            $inProgressOpps = Opportunity::where('partner_id', $partner->id)
                ->whereIn('stage', ['Value Proposition', 'Proposal/Price Quote', 'Negotiation/Review'])
                ->inRandomOrder()
                ->take($extraInProgress)
                ->get();

            foreach ($inProgressOpps as $opp) {
                $this->createProject($partner, $opp, 'In Progress', $partnerAccounts, $contacts, $satMin, $satMax);
                $projectCount++;
            }

            // Fill remaining with fabricated in-progress if needed
            $remaining = $extraInProgress - $inProgressOpps->count();
            if ($remaining > 0) {
                $this->createExtraProjects(
                    $partner, $remaining, 'In Progress', $partnerAccounts, $contacts, $satMin, $satMax, $projectCount
                );
                $projectCount += $remaining;
            }

            // ── 4. Planning/Draft projects from early pipeline ──
            $earlyOpps = Opportunity::where('partner_id', $partner->id)
                ->whereIn('stage', ['Needs Analysis', 'Value Proposition'])
                ->inRandomOrder()
                ->take($extraPlanning)
                ->get();

            foreach ($earlyOpps as $opp) {
                $this->createProject($partner, $opp, 'Draft', $partnerAccounts, $contacts, $satMin, $satMax);
                $projectCount++;
            }

            $remaining = $extraPlanning - $earlyOpps->count();
            if ($remaining > 0) {
                $this->createExtraProjects(
                    $partner, $remaining, 'Draft', $partnerAccounts, $contacts, $satMin, $satMax, $projectCount
                );
                $projectCount += $remaining;
            }
        }

        $this->command->info("Created {$projectCount} projects across statuses and product areas.");
    }

    private function createProject(
        Partner $partner,
        Opportunity $opp,
        string $status,
        $partnerAccounts,
        array $contacts,
        int $satMin,
        int $satMax,
    ): Project {
        $accountId = $partnerAccounts->isNotEmpty()
            ? $partnerAccounts->random()
            : (Account::inRandomOrder()->first()?->id ?? Account::factory()->create()->id);

        $startDate = $opp->close_date?->copy()->addDays(rand(7, 30)) ?? now()->subMonths(rand(1, 6));
        $totalSprints = rand(4, 14);

        $percentComplete = match ($status) {
            'Completed' => 100,
            'Draft' => 0,
            'On Hold' => fake()->numberBetween(10, 40),
            default => fake()->numberBetween(15, 85),
        };

        $currentSprint = match ($status) {
            'Completed' => $totalSprints,
            'Draft' => 0,
            default => (int) ceil($totalSprints * $percentComplete / 100),
        };

        $phase = match ($status) {
            'Completed' => 'live',
            'Draft' => 'kickoff',
            default => fake()->randomElement(array_merge(
                array_fill(0, 2, 'kickoff'),
                array_fill(0, 5, 'implementation'),
                array_fill(0, 3, 'live'),
            )),
        };

        // Go-live dates: completed = past, in-progress = future, draft = far future
        $goLiveDate = match ($status) {
            'Completed' => $startDate->copy()->addWeeks(rand(12, 36)),
            'In Progress' => now()->addWeeks(rand(4, 24)),
            'On Hold' => now()->addWeeks(rand(8, 30)),
            default => now()->addWeeks(rand(16, 40)),
        };

        // Outcome data for completed projects
        $customerSatisfaction = $status === 'Completed' ? rand($satMin, $satMax) : null;
        $isReferenceable = $status === 'Completed' && $customerSatisfaction >= 4 && rand(1, 3) <= 2;
        $ledToExpansion = $status === 'Completed' && rand(1, 4) === 1;
        $scopeChanges = $status === 'Completed' ? rand(0, 3) : 0;

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
            'total_sprints' => $totalSprints,
            'current_sprint' => $currentSprint,
            'delivery_team' => json_encode(fake()->words(rand(2, 5))),
            'go_live_date' => $goLiveDate,
            'customer_satisfaction' => $customerSatisfaction,
            'is_referenceable' => $isReferenceable,
            'led_to_expansion' => $ledToExpansion,
            'scope_changes' => $scopeChanges,
        ]);

        if (! empty($contacts)) {
            $project->contacts()->sync(
                collect($contacts)->random(min(count($contacts), rand(2, 5)))->all()
            );
        }

        return $project;
    }

    /**
     * Create extra fabricated projects (no opportunity link) for richer data.
     */
    private function createExtraProjects(
        Partner $partner,
        int $count,
        string $status,
        $partnerAccounts,
        array $contacts,
        int $satMin,
        int $satMax,
        int &$projectCount,
    ): void {
        $productAreas = ['Finance', 'Supply Chain', 'Sales', 'Workforce', 'Platform'];
        $areaWeights = [30, 25, 20, 15, 10];

        for ($i = 0; $i < $count; $i++) {
            $area = $this->weightedRandom($productAreas, $areaWeights);
            $templates = $this->projectNameTemplates[$area] ?? $this->projectNameTemplates['Finance'];
            $accountName = fake()->company();
            $projectName = str_replace('{account}', $accountName, $templates[array_rand($templates)]);

            $accountId = $partnerAccounts->isNotEmpty()
                ? $partnerAccounts->random()
                : (Account::inRandomOrder()->first()?->id ?? Account::factory()->create()->id);

            $totalSprints = rand(4, 14);

            $percentComplete = match ($status) {
                'Completed' => 100,
                'Draft' => 0,
                default => fake()->numberBetween(15, 85),
            };

            $currentSprint = match ($status) {
                'Completed' => $totalSprints,
                'Draft' => 0,
                default => (int) ceil($totalSprints * $percentComplete / 100),
            };

            $phase = match ($status) {
                'Completed' => 'live',
                'Draft' => 'kickoff',
                default => fake()->randomElement(['kickoff', 'implementation', 'implementation', 'implementation']),
            };

            $startDate = match ($status) {
                'Completed' => now()->subMonths(rand(6, 18)),
                'In Progress' => now()->subMonths(rand(1, 4)),
                default => now()->addWeeks(rand(2, 12)),
            };

            $goLiveDate = match ($status) {
                'Completed' => $startDate->copy()->addWeeks(rand(12, 36)),
                'In Progress' => now()->addWeeks(rand(4, 24)),
                default => now()->addWeeks(rand(16, 40)),
            };

            $customerSatisfaction = $status === 'Completed' ? rand($satMin, $satMax) : null;
            $isReferenceable = $status === 'Completed' && $customerSatisfaction >= 4 && rand(1, 3) <= 2;

            // Create a matching opportunity so product_area is available
            $opp = Opportunity::create([
                'salesforce_id' => 'OPP-PROJ-'.str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'opportunity_name' => $projectName,
                'account_name' => $accountName,
                'partner_id' => $partner->id,
                'stage' => $status === 'Completed' ? 'Closed Won' : 'Negotiation/Review',
                'amount' => round(rand(50000, 400000), -3),
                'close_date' => $status === 'Completed' ? $startDate->copy()->subDays(rand(7, 30)) : now()->addWeeks(rand(2, 8)),
                'type' => fake()->randomElement(['Implementation', 'Expansion', 'Migration', 'Platform Upgrade']),
                'revenue_type' => fake()->randomElement(['sourced', 'influenced', 'influenced']),
                'product_area' => $area,
                'lead_source' => 'Partner Referral',
                'owner' => fake()->name(),
                'probability' => $status === 'Completed' ? 100 : 80,
            ]);

            $project = Project::create([
                'partner_id' => $partner->id,
                'account_id' => $accountId,
                'opportunity_id' => $opp->id,
                'name' => $projectName,
                'start_date' => $startDate,
                'cal_id' => $contacts[0] ?? null,
                'tal_id' => $contacts[1] ?? null,
                'ae_id' => $contacts[2] ?? null,
                'acv' => $opp->amount,
                'status' => $status,
                'phase' => $phase,
                'percent_complete' => $percentComplete,
                'total_sprints' => $totalSprints,
                'current_sprint' => $currentSprint,
                'delivery_team' => json_encode(fake()->words(rand(2, 5))),
                'go_live_date' => $goLiveDate,
                'customer_satisfaction' => $customerSatisfaction,
                'is_referenceable' => $isReferenceable,
                'led_to_expansion' => $status === 'Completed' && rand(1, 4) === 1,
                'scope_changes' => $status === 'Completed' ? rand(0, 3) : 0,
            ]);

            if (! empty($contacts)) {
                $project->contacts()->sync(
                    collect($contacts)->random(min(count($contacts), rand(2, 5)))->all()
                );
            }
        }
    }

    private function ensurePartnerHasAccountsAndContacts(Partner $partner): void
    {
        if ($partner->accounts->isEmpty()) {
            $accounts = Account::inRandomOrder()->take(rand(1, 3))->get();
            if ($accounts->isNotEmpty()) {
                $partner->accounts()->attach($accounts->pluck('id'));
                $partner->load('accounts');
            }
        }

        if ($partner->contacts->isEmpty()) {
            Contact::factory()->count(rand(2, 5))->create([
                'partner_id' => $partner->id,
            ]);
            $partner->load('contacts');
        }
    }

    private function weightedRandom(array $items, array $weights): string
    {
        $rand = rand(1, array_sum($weights));
        $cumulative = 0;
        foreach ($weights as $idx => $w) {
            $cumulative += $w;
            if ($rand <= $cumulative) {
                return $items[$idx];
            }
        }

        return $items[0];
    }
}
