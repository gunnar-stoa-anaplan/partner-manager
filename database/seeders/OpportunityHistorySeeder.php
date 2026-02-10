<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\Opportunity;
use App\Models\OpportunitySnapshot;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunityHistorySeeder extends Seeder
{
    private array $stageProgression = [
        'Prospecting',
        'Qualification',
        'Needs Analysis',
        'Value Proposition',
        'Proposal/Price Quote',
        'Negotiation/Review',
        'Closed Won',
    ];

    private array $accounts = [
        'Coca-Cola', 'Nike', 'Tesla', 'Pfizer', 'Goldman Sachs', 'Target', 'Walmart',
        'Johnson & Johnson', 'Procter & Gamble', 'Unilever', 'AstraZeneca', 'Boeing',
        'Caterpillar', 'FedEx', 'UPS', 'Disney', 'Netflix', 'Spotify', 'Adobe', 'Salesforce',
        'SAP', 'Oracle', 'IBM', 'Dell', 'HP', 'Cisco', 'Intel', 'AMD', 'Qualcomm', 'Siemens',
        'BMW', 'Volkswagen', 'Toyota', 'Honda', 'Nestle', 'PepsiCo', 'Mondelez', 'Mars',
        'Shell', 'BP', 'TotalEnergies', 'Chevron', 'ExxonMobil', 'Rio Tinto', 'BHP',
        'Barclays', 'HSBC', 'Citi', 'JPMorgan', 'Morgan Stanley', 'BlackRock', 'Vanguard',
    ];

    private array $oppTypes = [
        'Implementation', 'Expansion', 'Migration', 'Strategic Planning',
        'Digital Transformation', 'Platform Upgrade', 'Consulting Engagement',
        'Managed Services', 'Training & Enablement', 'Integration',
    ];

    /**
     * Partner-specific profiles controlling opportunity volume and value.
     * [min_opps, max_opps, min_amount_influenced, max_amount_influenced, avg_close_weeks, sourced_chance_pct]
     *
     * sourced_chance_pct: percentage of opportunities that are sourced (vs influenced).
     * sourced_chance_pct controls the ratio of sourced vs influenced opportunities.
     * Sourced NNACV ends up roughly 3-40% of influenced since fewer opps are sourced.
     */
    private function getPartnerProfile(string $partnerName): array
    {
        return match (true) {
            str_contains($partnerName, 'Accenture') => [18, 24, 80000, 450000, 14, 25],
            str_contains($partnerName, 'Deloitte') => [15, 20, 60000, 380000, 16, 22],
            str_contains($partnerName, 'PwC')      => [14, 18, 50000, 320000, 15, 25],
            default                                 => [8, 12, 15000, 180000, 12, 20],
        };
    }

    public function run(): void
    {
        $this->command->info('Seeding opportunity history (52 weeks)...');

        // Clear only opportunity-related data (non-destructive to partners/users/contacts/etc.)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        OpportunitySnapshot::truncate();
        Opportunity::truncate();
        Import::where('type', 'opportunities')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $partners = Partner::orderBy('id')->get();
        $admin = User::where('role', 'admin')->first() ?? User::first();

        if ($partners->isEmpty()) {
            $this->command->warn('No partners found. Run PartnerSeeder first.');
            return;
        }

        $weeksToSimulate = 52;
        $startDate = now()->subWeeks($weeksToSimulate);

        // Pre-generate opportunities for each partner
        $partnerOpportunities = [];

        foreach ($partners as $partner) {
            [$minOpps, $maxOpps, $minAmt, $maxAmt, $avgCloseWeeks, $sourcedChance] = $this->getPartnerProfile($partner->name);
            $oppCount = rand($minOpps, $maxOpps);

            // Determine sourced count upfront (guarantees at least 1 sourced per partner)
            $sourcedCount = max(1, (int) round($oppCount * $sourcedChance / 100));
            $sourcedRemaining = $sourcedCount;

            $partnerOpps = [];
            for ($i = 0; $i < $oppCount; $i++) {
                $account = $this->accounts[array_rand($this->accounts)];
                $type = $this->oppTypes[array_rand($this->oppTypes)];

                // Assign sourced to the first N opportunities, rest are influenced
                $influencedRemaining = $oppCount - $i - $sourcedRemaining;
                $isSourced = $sourcedRemaining > 0 && ($influencedRemaining <= 0 || rand(1, $sourcedRemaining + $influencedRemaining) <= $sourcedRemaining);
                if ($isSourced) $sourcedRemaining--;
                $revenueType = $isSourced ? 'sourced' : 'influenced';

                // Stagger when opportunities are introduced (spread across weeks)
                $introWeek = rand(0, max(0, $weeksToSimulate - $avgCloseWeeks - 4));

                // Starting stage: earlier introductions start earlier in pipeline
                $startStageIdx = ($introWeek < 10) ? rand(0, 2) : rand(0, 1);

                $partnerOpps[] = [
                    'partner_id' => $partner->id,
                    'account_name' => $account,
                    'opportunity_name' => "{$account} - {$type}",
                    'amount' => round(rand($minAmt * 100, $maxAmt * 100) / 100, 2),
                    'type' => $type,
                    'revenue_type' => $revenueType,
                    'intro_week' => $introWeek,
                    'current_stage_index' => $startStageIdx,
                    'weeks_in_stage' => 0,
                    'weeks_to_advance' => rand(3, 6),
                    'close_date' => $startDate->copy()->addWeeks($introWeek + $avgCloseWeeks + rand(-3, 5)),
                    'lead_source' => $isSourced
                        ? 'Partner Referral'
                        : fake()->randomElement(['Web', 'Event', 'Cold Call', 'LinkedIn']),
                    'owner' => fake()->name(),
                    'db_id' => null,
                    'closed' => false,
                ];
            }

            $partnerOpportunities[$partner->id] = $partnerOpps;
        }

        $totalSnapshots = 0;
        $totalOpportunities = 0;

        for ($week = 0; $week < $weeksToSimulate; $week++) {
            $weekDate = $startDate->copy()->addWeeks($week);

            // Create weekly import record
            $import = Import::create([
                'type' => 'opportunities',
                'file_name' => 'weekly_pipeline_' . $weekDate->format('Y_m_d') . '.xlsx',
                'file_path' => 'imports/weekly_pipeline_' . $weekDate->format('Y_m_d') . '.xlsx',
                'status' => 'completed',
                'user_id' => $admin->id,
                'started_at' => $weekDate,
                'completed_at' => $weekDate->copy()->addMinutes(rand(2, 8)),
                'total_rows' => 0,
                'successful_rows' => 0,
                'failed_rows' => 0,
                'partners_created' => 0,
                'partners_updated' => 0,
                'opportunities_created' => 0,
                'opportunities_updated' => 0,
                'resources_created' => 0,
            ]);

            $weekSnapshots = [];
            $weekNewOpps = 0;

            foreach ($partnerOpportunities as $partnerId => &$opps) {
                foreach ($opps as &$opp) {
                    // Skip if not yet introduced
                    if ($week < $opp['intro_week']) continue;

                    // Skip if already closed
                    if ($opp['closed']) continue;

                    // Create the DB opportunity if first appearance
                    if ($opp['db_id'] === null) {
                        $fiscalQuarter = 'Q' . ceil($opp['close_date']->month / 3);
                        $fiscalYear = $opp['close_date']->year;

                        $dbOpp = Opportunity::create([
                            'salesforce_id' => 'OPP-' . str_pad(++$totalOpportunities, 6, '0', STR_PAD_LEFT),
                            'opportunity_name' => $opp['opportunity_name'],
                            'account_name' => $opp['account_name'],
                            'partner_id' => $opp['partner_id'],
                            'stage' => $this->stageProgression[$opp['current_stage_index']],
                            'amount' => $opp['amount'],
                            'close_date' => $opp['close_date'],
                            'fiscal_quarter' => $fiscalQuarter,
                            'fiscal_year' => $fiscalYear,
                            'type' => $opp['type'],
                            'lead_source' => $opp['lead_source'],
                            'revenue_type' => $opp['revenue_type'],
                            'probability' => $this->getProbabilityForStage($this->stageProgression[$opp['current_stage_index']]),
                            'owner' => $opp['owner'],
                            'import_id' => $import->id,
                        ]);
                        $opp['db_id'] = $dbOpp->id;
                        $weekNewOpps++;
                    }

                    // Evolve stage
                    $opp['weeks_in_stage']++;
                    if ($opp['weeks_in_stage'] >= $opp['weeks_to_advance'] && $opp['current_stage_index'] < count($this->stageProgression) - 1) {
                        $opp['current_stage_index']++;
                        $opp['weeks_in_stage'] = 0;
                        $opp['weeks_to_advance'] = rand(3, 6);

                        $newStage = $this->stageProgression[$opp['current_stage_index']];

                        // Small chance of amount adjustment as deal progresses
                        if (rand(1, 4) === 1) {
                            $opp['amount'] = round($opp['amount'] * (rand(85, 120) / 100), 2);
                        }

                        // Mark as closed if reached Closed Won
                        if ($newStage === 'Closed Won') {
                            $opp['closed'] = true;
                        }

                        Opportunity::where('id', $opp['db_id'])->update([
                            'stage' => $newStage,
                            'probability' => $this->getProbabilityForStage($newStage),
                            'amount' => $opp['amount'],
                            'import_id' => $import->id,
                        ]);
                    }

                    // Create snapshot
                    $currentStage = $this->stageProgression[$opp['current_stage_index']];
                    $weekSnapshots[] = [
                        'opportunity_id' => $opp['db_id'],
                        'import_id' => $import->id,
                        'snapshot_date' => $weekDate->toDateString(),
                        'opportunity_name' => $opp['opportunity_name'],
                        'account_name' => $opp['account_name'],
                        'partner_id' => $opp['partner_id'],
                        'stage' => $currentStage,
                        'amount' => $opp['amount'],
                        'close_date' => $opp['close_date']->toDateString(),
                        'fiscal_quarter' => null,
                        'fiscal_year' => null,
                        'type' => $opp['type'],
                        'lead_source' => $opp['lead_source'],
                        'revenue_type' => $opp['revenue_type'],
                        'probability' => $this->getProbabilityForStage($currentStage),
                        'owner' => $opp['owner'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            unset($opps, $opp);

            // Batch insert snapshots
            if (!empty($weekSnapshots)) {
                foreach (array_chunk($weekSnapshots, 500) as $chunk) {
                    DB::table('opportunity_snapshots')->insert($chunk);
                }
                $totalSnapshots += count($weekSnapshots);
            }

            // Update import stats
            $import->update([
                'total_rows' => count($weekSnapshots),
                'successful_rows' => count($weekSnapshots),
                'opportunities_created' => $weekNewOpps,
                'opportunities_updated' => count($weekSnapshots) - $weekNewOpps,
            ]);
        }

        $this->command->info('');
        $this->command->info("Seeded {$totalOpportunities} opportunities across " . $partners->count() . " partners.");
        $this->command->info("Created {$totalSnapshots} snapshots over {$weeksToSimulate} weekly imports.");

        // Show per-partner breakdown with sourced/influenced split
        foreach ($partners->take(5) as $partner) {
            $sourced = Opportunity::where('partner_id', $partner->id)->where('revenue_type', 'sourced');
            $influenced = Opportunity::where('partner_id', $partner->id)->where('revenue_type', 'influenced');
            $sCount = $sourced->count();
            $iCount = $influenced->count();
            $sAcv = (clone $sourced)->sum('amount');
            $iAcv = (clone $influenced)->sum('amount');
            $ratio = $iAcv > 0 ? round($sAcv / $iAcv * 100, 1) : 0;
            $this->command->info("  {$partner->name}: {$sCount} sourced (\${$this->fmtK($sAcv)}) + {$iCount} influenced (\${$this->fmtK($iAcv)}) = {$ratio}% ratio");
        }
        $this->command->info("  ... and " . max(0, $partners->count() - 5) . " more partners");
    }

    private function fmtK(float $val): string
    {
        return $val >= 1000000
            ? number_format($val / 1000000, 1) . 'M'
            : number_format($val / 1000, 0) . 'k';
    }

    private function getProbabilityForStage(string $stage): int
    {
        return match ($stage) {
            'Prospecting' => 10,
            'Qualification' => 20,
            'Needs Analysis' => 30,
            'Value Proposition' => 40,
            'Proposal/Price Quote' => 60,
            'Negotiation/Review' => 80,
            'Closed Won' => 100,
            'Closed Lost' => 0,
            default => 50,
        };
    }
}
