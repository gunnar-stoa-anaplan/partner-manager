<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\LaunchProgramPartner;
use App\Models\Partner;
use App\Models\PartnerAssignedGoal;
use App\Models\PartnerHealthSnapshot;
use App\Models\PartnerScorecard;
use Illuminate\Support\Facades\DB;

class PartnerHealthService
{
    /**
     * Dimension weights for health score computation.
     *
     * @var array<string, float>
     */
    protected array $weights = [
        'scorecard' => 0.25,
        'pipeline_trend' => 0.20,
        'goal_attainment' => 0.20,
        'enablement' => 0.15,
        'contact_freshness' => 0.10,
        'delivery_csat' => 0.10,
    ];

    /**
     * Compute health score and signals for a partner.
     *
     * @return array{score: float, risk_level: string, signals: array<string>, dimensions: array<string, float>}
     */
    public function computeForPartner(Partner $partner): array
    {
        $dimensions = [
            'scorecard' => $this->scorecardDimension($partner),
            'pipeline_trend' => $this->pipelineTrendDimension($partner),
            'goal_attainment' => $this->goalAttainmentDimension($partner),
            'enablement' => $this->enablementDimension($partner),
            'contact_freshness' => $this->contactFreshnessDimension($partner),
            'delivery_csat' => $this->deliveryCsatDimension($partner),
        ];

        $score = 0;
        foreach ($dimensions as $key => $value) {
            $score += $value * ($this->weights[$key] ?? 0);
        }
        $score = round($score, 1);

        $signals = $this->generateSignals($partner, $dimensions);

        $riskLevel = match (true) {
            $score >= 70 => 'healthy',
            $score >= 50 => 'watch',
            $score >= 30 => 'at_risk',
            default => 'critical',
        };

        return [
            'score' => $score,
            'risk_level' => $riskLevel,
            'signals' => $signals,
            'dimensions' => $dimensions,
        ];
    }

    /**
     * Compute and store a daily snapshot for a partner.
     */
    public function snapshotForPartner(Partner $partner): PartnerHealthSnapshot
    {
        $result = $this->computeForPartner($partner);

        return PartnerHealthSnapshot::updateOrCreate(
            ['partner_id' => $partner->id, 'snapshot_date' => now()->toDateString()],
            [
                'score' => $result['score'],
                'risk_level' => $result['risk_level'],
                'signals' => $result['signals'],
                'dimensions' => $result['dimensions'],
            ]
        );
    }

    /**
     * Snapshot all partners. Returns count.
     */
    public function snapshotAll(): int
    {
        $count = 0;
        Partner::select('id')->chunk(50, function ($partners) use (&$count) {
            foreach ($partners as $p) {
                $partner = Partner::withoutGlobalScopes()->find($p->id);
                $this->snapshotForPartner($partner);
                $count++;
            }
        });

        return $count;
    }

    protected function scorecardDimension(Partner $partner): float
    {
        $scorecard = PartnerScorecard::where('partner_id', $partner->id)
            ->orderByDesc('scored_at')
            ->first();

        return $scorecard ? min(100, max(0, $scorecard->overall_score)) : 50;
    }

    protected function pipelineTrendDimension(Partner $partner): float
    {
        $currentPipeline = DB::table('opportunities')
            ->where('partner_id', $partner->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');

        $historicalPipeline = DB::table('opportunity_snapshots')
            ->where('partner_id', $partner->id)
            ->where('snapshot_date', '<=', now()->subDays(90))
            ->where('snapshot_date', '>=', now()->subDays(120))
            ->whereNotNull('stage')
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');

        if ($historicalPipeline <= 0) {
            return $currentPipeline > 0 ? 70 : 40;
        }

        $change = ($currentPipeline - $historicalPipeline) / $historicalPipeline;

        return match (true) {
            $change >= 0.20 => 100,
            $change >= 0 => 75,
            $change >= -0.20 => 50,
            $change >= -0.40 => 25,
            default => 0,
        };
    }

    protected function goalAttainmentDimension(Partner $partner): float
    {
        $goals = PartnerAssignedGoal::where('partner_id', $partner->id)
            ->where('fiscal_year', $this->currentFiscalYear())
            ->where('target_value', '>', 0)
            ->get();

        if ($goals->isEmpty()) {
            return 50;
        }

        $totalAttainment = $goals->sum(fn ($g) => min(100, $g->getAttainmentPercentage()));

        return min(100, round($totalAttainment / $goals->count(), 1));
    }

    protected function enablementDimension(Partner $partner): float
    {
        $recentEvents = DB::table('partner_events')
            ->where('partner_id', $partner->id)
            ->where('start_at', '>=', now()->subDays(90))
            ->count();
        $eventScore = min(100, ($recentEvents / 6) * 100);

        $attended = DB::table('enablement_registrations')
            ->where('partner_id', $partner->id)
            ->where('status', 'attended')
            ->count();
        $registered = DB::table('enablement_registrations')
            ->where('partner_id', $partner->id)
            ->count();
        $attendRate = $registered > 0 ? ($attended / $registered) : 0;
        $attendScore = min(100, ($attendRate / 0.80) * 100);

        $bpTotal = DB::table('partner_blueprint_assignments')
            ->where('partner_id', $partner->id)->count();
        $bpCompleted = DB::table('partner_blueprint_assignments')
            ->where('partner_id', $partner->id)
            ->where('status', 'completed')->count();
        $bpScore = $bpTotal > 0 ? ($bpCompleted / $bpTotal) * 100 : 50;

        return round(($eventScore * 0.40) + ($attendScore * 0.30) + ($bpScore * 0.30), 1);
    }

    protected function contactFreshnessDimension(Partner $partner): float
    {
        $latestInteraction = DB::table('contact_interactions')
            ->where('partner_id', $partner->id)
            ->max('interaction_date');

        if (! $latestInteraction) {
            $latestUpdate = DB::table('contacts')
                ->where('partner_id', $partner->id)
                ->max('updated_at');

            $daysSince = $latestUpdate ? now()->diffInDays($latestUpdate) : 180;
        } else {
            $daysSince = now()->diffInDays($latestInteraction);
        }

        return match (true) {
            $daysSince <= 14 => 100,
            $daysSince <= 30 => 80,
            $daysSince <= 60 => 60,
            $daysSince <= 90 => 40,
            $daysSince <= 180 => 20,
            default => 0,
        };
    }

    protected function deliveryCsatDimension(Partner $partner): float
    {
        $avgCsat = DB::table('projects')
            ->where('partner_id', $partner->id)
            ->where('status', 'completed')
            ->whereNotNull('customer_satisfaction')
            ->avg('customer_satisfaction');

        if ($avgCsat === null) {
            return 50;
        }

        // Scale 1-5 to 0-100
        return min(100, max(0, (($avgCsat - 1) / 4) * 100));
    }

    /**
     * @return array<string>
     */
    protected function generateSignals(Partner $partner, array $dimensions): array
    {
        $signals = [];

        if ($dimensions['pipeline_trend'] <= 25) {
            $signals[] = 'Pipeline declined significantly over last 90 days';
        }

        if ($dimensions['goal_attainment'] <= 30) {
            $signals[] = 'Goal attainment below 30% — falling behind targets';
        }

        if ($dimensions['contact_freshness'] <= 20) {
            $signals[] = 'No contact interactions in 90+ days — engagement at risk';
        }

        if ($dimensions['enablement'] <= 25) {
            $signals[] = 'Low enablement activity — training and blueprint completion stalled';
        }

        if ($dimensions['delivery_csat'] <= 25) {
            $signals[] = 'Customer satisfaction below expectations';
        }

        if ($dimensions['scorecard'] <= 30) {
            $signals[] = 'Overall scorecard score critically low';
        }

        $staleDeals = DB::table('opportunities')
            ->where('partner_id', $partner->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->where('updated_at', '<', now()->subDays(45))
            ->count();
        if ($staleDeals >= 3) {
            $signals[] = "{$staleDeals} opportunities stale for 45+ days";
        }

        $expiringCerts = DB::table('certifications')
            ->where('partner_id', $partner->id)
            ->where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays(60)])
            ->count();
        if ($expiringCerts >= 2) {
            $signals[] = "{$expiringCerts} certifications expiring within 60 days";
        }

        // Launch program risk signals
        $enrollments = LaunchProgramPartner::where('partner_id', $partner->id)
            ->whereNotIn('status', ['withdrawn'])
            ->with(['program.milestones', 'participants.milestoneCompletions'])
            ->get();

        foreach ($enrollments as $enrollment) {
            $prog = $enrollment->program;
            if ($prog->status !== 'active') {
                continue;
            }

            $milestones = $prog->milestones;
            $completedIds = $enrollment->participants
                ->flatMap(fn ($p) => $p->milestoneCompletions->pluck('launch_program_milestone_id'))
                ->unique();
            $incompleteRequired = $milestones->where('is_required', true)
                ->reject(fn ($ms) => $completedIds->contains($ms->id));

            if ($incompleteRequired->isNotEmpty()) {
                $daysToGa = $prog->target_ga_date ? (int) now()->diffInDays($prog->target_ga_date, false) : null;
                $urgency = $daysToGa !== null && $daysToGa <= 30 ? ' — GA in '.$daysToGa.' days' : '';
                $signals[] = $prog->name.': '.$incompleteRequired->count().' required milestone'.($incompleteRequired->count() > 1 ? 's' : '').' incomplete'.$urgency;
            }
        }

        return $signals;
    }

    protected function currentFiscalYear(): string
    {
        $fyStart = (int) AppSetting::get('fiscal_year_start_month', 2);
        $now = now();
        $year = $now->month >= $fyStart ? $now->year : $now->year - 1;

        return 'FY'.substr($year + 1, 2);
    }
}
