<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerAssignedGoal;
use App\Models\PartnerScorecard;
use App\Support\DatabasePortability;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BenchmarkService
{
    const DIMENSIONS = [
        'pipeline' => 'Pipeline Value',
        'certifications' => 'Certifications',
        'win_rate' => 'Win Rate',
        'delivery' => 'Delivery Score',
        'enablement' => 'Enablement Score',
        'goal_attainment' => 'Goal Attainment',
    ];

    const QUARTILE_LABELS = [
        4 => 'Top 25%',
        3 => 'Above Average',
        2 => 'Below Average',
        1 => 'Bottom 25%',
    ];

    const QUARTILE_COLORS = [
        4 => 'success',
        3 => 'info',
        2 => 'warning',
        1 => 'danger',
    ];

    /**
     * Compute benchmarks for all partners. Returns array keyed by partner_id.
     *
     * @return array<int, array{dimensions: array, overall_percentile: float, overall_quartile: int}>
     */
    public function computeAll(Collection $partnerIds): array
    {
        $rawData = $this->gatherRawData($partnerIds);

        if ($rawData->isEmpty()) {
            return [];
        }

        $benchmarks = [];
        foreach (self::DIMENSIONS as $dimKey => $dimLabel) {
            $values = $rawData->pluck($dimKey)->filter(fn ($v) => $v !== null)->sort()->values();
            $count = $values->count();

            foreach ($rawData as $partnerId => $data) {
                $value = $data[$dimKey] ?? 0;
                $rank = $values->search(fn ($v) => $v >= $value);
                $rank = $rank === false ? $count - 1 : $rank;
                $percentile = $count > 1 ? round(($rank / ($count - 1)) * 100, 1) : 50;
                $quartile = match (true) {
                    $percentile >= 75 => 4,
                    $percentile >= 50 => 3,
                    $percentile >= 25 => 2,
                    default => 1,
                };

                $benchmarks[$partnerId]['dimensions'][$dimKey] = [
                    'value' => round($value, 2),
                    'percentile' => $percentile,
                    'quartile' => $quartile,
                    'quartile_label' => self::QUARTILE_LABELS[$quartile],
                ];
            }
        }

        // Compute overall percentile (average of dimension percentiles)
        foreach ($benchmarks as $partnerId => &$bm) {
            $avgPct = collect($bm['dimensions'])->avg('percentile');
            $bm['overall_percentile'] = round($avgPct, 1);
            $bm['overall_quartile'] = match (true) {
                $avgPct >= 75 => 4,
                $avgPct >= 50 => 3,
                $avgPct >= 25 => 2,
                default => 1,
            };
        }

        return $benchmarks;
    }

    /**
     * Compute benchmarks for a single partner within the context of all partners.
     *
     * @return array{dimensions: array, overall_percentile: float, overall_quartile: int, portfolio_averages: array}
     */
    public function computeForPartner(Partner $partner, Collection $allPartnerIds): array
    {
        $all = $this->computeAll($allPartnerIds);
        $partnerBm = $all[$partner->id] ?? ['dimensions' => [], 'overall_percentile' => 0, 'overall_quartile' => 1];

        // Compute portfolio averages
        $portfolioAvg = [];
        foreach (self::DIMENSIONS as $dimKey => $dimLabel) {
            $values = collect($all)->pluck("dimensions.{$dimKey}.value")->filter();
            $portfolioAvg[$dimKey] = $values->isNotEmpty() ? round($values->avg(), 2) : 0;
        }

        $partnerBm['portfolio_averages'] = $portfolioAvg;

        return $partnerBm;
    }

    /**
     * @return Collection<int, array{pipeline: float, certifications: int, win_rate: float, delivery: float, enablement: float, goal_attainment: float}>
     */
    protected function gatherRawData(Collection $partnerIds): Collection
    {
        $data = collect();

        // Pipeline values
        $pipelines = DB::table('opportunities')
            ->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->groupBy('partner_id')
            ->selectRaw('partner_id, SUM(amount) as total')
            ->pluck('total', 'partner_id');

        // Certification counts
        $certs = DB::table('certifications')
            ->whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->groupBy('partner_id')
            ->selectRaw('partner_id, COUNT(*) as total')
            ->pluck('total', 'partner_id');

        // Win rates
        $winData = DB::table('opportunities')
            ->whereIn('partner_id', $partnerIds)
            ->whereIn('stage', ['Closed Won', 'Closed Lost'])
            ->where('close_date', '>=', now()->subMonths(12))
            ->groupBy('partner_id')
            ->selectRaw('partner_id, SUM(CASE WHEN stage = \'Closed Won\' THEN 1 ELSE 0 END) as won, COUNT(*) as total')
            ->get()
            ->keyBy('partner_id');

        // Scorecard dimension scores. The read is already bounded to the
        // caller-supplied population ($partnerIds); bypass the row-level scope
        // so the benchmark is computed over exactly that set, not re-filtered to
        // the current actor's visibility.
        $scorecards = PartnerScorecard::withoutGlobalScope('actor_visibility')
            ->whereIn('partner_id', $partnerIds)
            ->orderByDesc('scored_at')
            ->get()
            ->unique('partner_id')
            ->keyBy('partner_id');

        // Goal attainment — bounded to the caller-supplied population.
        $goalData = PartnerAssignedGoal::withoutGlobalScope('actor_visibility')
            ->whereIn('partner_id', $partnerIds)
            ->where('target_value', '>', 0)
            ->groupBy('partner_id')
            ->selectRaw('partner_id, AVG('.DatabasePortability::least('(actual_value / target_value) * 100', '100').') as avg_attainment')
            ->pluck('avg_attainment', 'partner_id');

        foreach ($partnerIds as $pid) {
            $wd = $winData->get($pid);
            $sc = $scorecards->get($pid);

            $data[$pid] = [
                'pipeline' => (float) ($pipelines[$pid] ?? 0),
                'certifications' => (float) ($certs[$pid] ?? 0),
                'win_rate' => $wd && $wd->total > 0 ? round(($wd->won / $wd->total) * 100, 1) : 0,
                'delivery' => $sc ? (float) $sc->delivery_score : 0,
                'enablement' => $sc ? (float) $sc->engagement_score : 0,
                'goal_attainment' => (float) ($goalData[$pid] ?? 0),
            ];
        }

        return $data;
    }
}
