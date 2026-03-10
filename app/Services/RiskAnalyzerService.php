<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RiskAnalyzerService
{
    private const LOB_TO_PROGRAM = [
        'Finance' => 'IFP',
        'Supply Chain' => 'I&D',
        'Sales and Marketing' => 'SPM',
        'Sales' => 'SPM',
        'HR' => 'OWP',
        'HR & Workforce' => 'OWP',
        'Workforce' => 'OWP',
        'Platform' => 'IBP',
    ];

    private const PROGRAMS = ['IFP', 'OWP', 'FCR', 'SPM', 'I&D', 'IBP', 'MFP'];

    private ?int $fiscalYear = null;

    /**
     * Resolve the resource import_id for a given snapshot date.
     */
    private function resourceImportId(string $snapshotDate): ?int
    {
        return DB::table('imports')
            ->where('type', 'resources')
            ->whereDate('created_at', $snapshotDate)
            ->value('id');
    }

    /**
     * Scope all opportunity queries to a specific fiscal year.
     */
    public function forFiscalYear(?int $year): static
    {
        $this->fiscalYear = $year;

        return $this;
    }

    /**
     * Get all available snapshot dates, ordered chronologically.
     *
     * @return Collection<int, string>
     */
    public function snapshotDates(): Collection
    {
        return DB::table('opportunity_snapshots')
            ->distinct()
            ->pluck('snapshot_date')
            ->sort()
            ->values();
    }

    /**
     * Portfolio-level KPIs for a given snapshot date and partner scope.
     *
     * @return array{
     *     total_nnacv: float,
     *     opp_count: int,
     *     resource_count: int,
     *     partner_count: int,
     *     sourced_count: int,
     *     sourced_nnacv: float,
     *     cs_health: array{red: int, yellow: int, green: int},
     *     delivery_assurance_count: int,
     *     delivery_assurance_pct: float,
     *     app_count: int,
     *     app_nnacv: float,
     *     platform_count: int,
     *     platform_nnacv: float,
     * }
     */
    public function portfolioSummary(string $snapshotDate, ?Collection $partnerIds = null): array
    {
        $opps = $this->snapshotOpportunities($snapshotDate, $partnerIds);

        // Separate pipeline (stages 1-4) from closed won (stage 5)
        $pipelineOpps = $opps->filter(fn ($o) => ! str_starts_with($o->stage ?? '', '5.'));
        $closedWonOpps = $opps->filter(fn ($o) => str_starts_with($o->stage ?? '', '5.'));

        $totalNnacv = $pipelineOpps->sum('amount');
        $partnerSourced = $pipelineOpps->where('revenue_type', 'sourced');

        // CS Health from excel_data
        $csHealth = ['red' => 0, 'yellow' => 0, 'green' => 0];
        $daCount = 0;
        $appCount = 0;
        $appNnacv = 0.0;
        $platformCount = 0;
        $platformNnacv = 0.0;

        foreach ($pipelineOpps as $opp) {
            $extra = is_string($opp->excel_data) ? json_decode($opp->excel_data, true) : ($opp->excel_data ?? []);

            $health = strtolower($extra['cs_health_score'] ?? '');
            if (isset($csHealth[$health])) {
                $csHealth[$health]++;
            }

            if (! empty($extra['delivery_assurance']) || $extra['delivery_assurance'] === true) {
                $daCount++;
            }

            if (isset($extra['app'])) {
                if ($extra['app'] === true || $extra['app'] === 1) {
                    $appCount++;
                    $appNnacv += (float) $opp->amount;
                } else {
                    $platformCount++;
                    $platformNnacv += (float) $opp->amount;
                }
            }
        }

        // Unique completed resources by role (scoped to snapshot)
        $resourceImportId = $this->resourceImportId($snapshotDate);
        $resourceByRole = DB::table('resources')
            ->where('status', 'Completed')
            ->when($resourceImportId, fn ($q) => $q->where('import_id', $resourceImportId))
            ->when($partnerIds, fn ($q) => $q->whereIn('partner_id', $partnerIds))
            ->select('role', DB::raw('COUNT(DISTINCT name) as cnt'))
            ->groupBy('role')
            ->pluck('cnt', 'role');

        $uniqueResources = $resourceByRole->sum();

        return [
            'total_nnacv' => $totalNnacv,
            'opp_count' => $pipelineOpps->count(),
            'resource_count' => $uniqueResources,
            'resources_by_role' => [
                'sales' => $resourceByRole->get('Sales', 0),
                'pre_sales' => $resourceByRole->get('Pre-Sales', 0),
                'delivery' => $resourceByRole->get('Delivery', 0),
            ],
            'partner_count' => $pipelineOpps->pluck('partner_id')->unique()->count(),
            'sourced_count' => $partnerSourced->count(),
            'sourced_nnacv' => $partnerSourced->sum('amount'),
            'cs_health' => $csHealth,
            'delivery_assurance_count' => $daCount,
            'delivery_assurance_pct' => $pipelineOpps->count() > 0 ? round(($daCount / $pipelineOpps->count()) * 100, 1) : 0,
            'app_count' => $appCount,
            'app_nnacv' => $appNnacv,
            'platform_count' => $platformCount,
            'platform_nnacv' => $platformNnacv,
            'closed_won_count' => $closedWonOpps->count(),
            'closed_won_nnacv' => $closedWonOpps->sum('amount'),
            'closed_won_velocity' => $this->calculateVelocity($closedWonOpps, $snapshotDate),
            'pipeline_by_lob' => $pipelineOpps->groupBy(fn ($o) => $o->product_area ?: 'Unknown')
                ->map(fn ($group, $lob) => ['lob' => $lob, 'count' => $group->count(), 'nnacv' => $group->sum('amount')])
                ->sortByDesc('nnacv')
                ->values()
                ->all(),
            'closed_won_by_lob' => $closedWonOpps->groupBy(fn ($o) => $o->product_area ?: 'Unknown')
                ->map(fn ($group, $lob) => ['lob' => $lob, 'count' => $group->count(), 'nnacv' => $group->sum('amount')])
                ->sortByDesc('nnacv')
                ->values()
                ->all(),
        ];
    }

    /**
     * Dimensional breakdown (geo, region, LOB, stage, quarter).
     *
     * @return Collection<string, array{opps: int, nnacv: float, resources: int}>
     */
    public function dimensionBreakdown(string $snapshotDate, string $dimension, ?Collection $partnerIds = null): Collection
    {
        $opps = $this->snapshotOpportunities($snapshotDate, $partnerIds);

        $grouped = $opps->groupBy(function ($opp) use ($dimension) {
            $extra = is_string($opp->excel_data) ? json_decode($opp->excel_data, true) : ($opp->excel_data ?? []);

            return match ($dimension) {
                'geo' => $extra['geo'] ?? 'Unknown',
                'region' => $extra['region'] ?? 'Unknown',
                'lob' => $opp->product_area ?? 'Unknown',
                'stage' => $opp->stage ?? 'Unknown',
                'quarter' => $opp->fiscal_quarter ?? 'Unknown',
                default => 'Unknown',
            };
        });

        return $grouped->map(function ($items, $key) {
            return [
                'opps' => $items->count(),
                'nnacv' => $items->sum('amount'),
                'partner_ids' => $items->pluck('partner_id')->unique()->values(),
            ];
        })->sortByDesc('nnacv');
    }

    /**
     * Historical trends across all snapshots.
     *
     * @return Collection<int, array{date: string, nnacv: float, opps: int, resources: int, app_nnacv: float, platform_nnacv: float}>
     */
    public function historicalTrends(?Collection $partnerIds = null): Collection
    {
        $dates = $this->snapshotDates();

        return $dates->map(function ($date) use ($partnerIds) {
            $summary = $this->portfolioSummary($date, $partnerIds);

            $totalDeals = $summary['opp_count'] + $summary['closed_won_count'];
            $winRate = $totalDeals > 0 ? round(($summary['closed_won_count'] / $totalDeals) * 100, 1) : 0;

            $csTotal = array_sum($summary['cs_health'] ?? []);
            $redPct = $csTotal > 0 ? round(($summary['cs_health']['red'] ?? 0) / $csTotal * 100) : 0;
            $sourcedPct = $summary['opp_count'] > 0
                ? round(($summary['sourced_count'] / $summary['opp_count']) * 100)
                : 0;

            return [
                'date' => $date,
                'nnacv' => $summary['total_nnacv'],
                'opps' => $summary['opp_count'],
                'resources' => $summary['resource_count'],
                'app_nnacv' => $summary['app_nnacv'],
                'platform_nnacv' => $summary['platform_nnacv'],
                'sourced_nnacv' => $summary['sourced_nnacv'],
                'sourced_count' => $summary['sourced_count'],
                'sourced_pct' => $sourcedPct,
                'closed_won_nnacv' => $summary['closed_won_nnacv'],
                'closed_won_count' => $summary['closed_won_count'],
                'win_rate' => $winRate,
                'red_pct' => $redPct,
            ];
        });
    }

    /**
     * Week-over-week delta between latest and previous snapshot.
     *
     * @return array{current: array, previous: array|null, deltas: array|null}
     */
    public function weekOverWeekDeltas(?Collection $partnerIds = null): array
    {
        $dates = $this->snapshotDates();

        if ($dates->count() < 1) {
            return ['current' => [], 'previous' => null, 'deltas' => null];
        }

        $current = $this->portfolioSummary($dates->last(), $partnerIds);

        if ($dates->count() < 2) {
            return ['current' => $current, 'previous' => null, 'deltas' => null];
        }

        $previous = $this->portfolioSummary($dates[$dates->count() - 2], $partnerIds);

        $deltas = [
            'nnacv' => $current['total_nnacv'] - $previous['total_nnacv'],
            'opps' => $current['opp_count'] - $previous['opp_count'],
            'resources' => $current['resource_count'] - $previous['resource_count'],
            'sourced_nnacv' => $current['sourced_nnacv'] - $previous['sourced_nnacv'],
            'sourced_count' => $current['sourced_count'] - $previous['sourced_count'],
            'da_pct' => $current['delivery_assurance_pct'] - $previous['delivery_assurance_pct'],
            'closed_won_count' => $current['closed_won_count'] - $previous['closed_won_count'],
            'closed_won_nnacv' => $current['closed_won_nnacv'] - $previous['closed_won_nnacv'],
            'win_rate' => (function () use ($current, $previous) {
                $curTotal = ($current['opp_count'] ?? 0) + ($current['closed_won_count'] ?? 0);
                $prevTotal = ($previous['opp_count'] ?? 0) + ($previous['closed_won_count'] ?? 0);
                $curWr = $curTotal > 0 ? round(($current['closed_won_count'] ?? 0) / $curTotal * 100, 1) : 0;
                $prevWr = $prevTotal > 0 ? round(($previous['closed_won_count'] ?? 0) / $prevTotal * 100, 1) : 0;

                return $curWr - $prevWr;
            })(),
        ];

        return ['current' => $current, 'previous' => $previous, 'deltas' => $deltas];
    }

    /**
     * Opportunity movement between two snapshot dates.
     *
     * @return array{new: Collection, dropped: Collection, stage_changes: Collection, amount_changes: Collection}
     */
    public function opportunityMovement(string $fromDate, string $toDate, ?Collection $partnerIds = null): array
    {
        $fromOpps = $this->snapshotOpportunities($fromDate, $partnerIds)->keyBy('opportunity_id');
        $toOpps = $this->snapshotOpportunities($toDate, $partnerIds)->keyBy('opportunity_id');

        $fromIds = $fromOpps->keys();
        $toIds = $toOpps->keys();

        $newOpps = $toOpps->only($toIds->diff($fromIds))->values();
        $droppedOpps = $fromOpps->only($fromIds->diff($toIds))->values();

        $stageChanges = collect();
        $amountChanges = collect();

        foreach ($fromIds->intersect($toIds) as $id) {
            $from = $fromOpps[$id];
            $to = $toOpps[$id];

            if ($from->stage !== $to->stage) {
                $stageChanges->push([
                    'opportunity_name' => $to->opportunity_name,
                    'partner_name' => $to->partner_name ?? '',
                    'from_stage' => $from->stage,
                    'to_stage' => $to->stage,
                    'amount' => $to->amount,
                ]);
            }

            if (abs((float) $from->amount - (float) $to->amount) > 1) {
                $amountChanges->push([
                    'opportunity_name' => $to->opportunity_name,
                    'partner_name' => $to->partner_name ?? '',
                    'from_amount' => $from->amount,
                    'to_amount' => $to->amount,
                    'delta' => (float) $to->amount - (float) $from->amount,
                ]);
            }
        }

        return [
            'new' => $newOpps,
            'dropped' => $droppedOpps,
            'stage_changes' => $stageChanges->sortByDesc('amount')->values(),
            'amount_changes' => $amountChanges->sortByDesc(fn ($c) => abs($c['delta']))->values(),
        ];
    }

    /**
     * Resource movement between two snapshot dates.
     *
     * @return array{new_resources: Collection, departed: Collection, new_certifications: Collection}
     */
    public function resourceMovement(string $fromDate, string $toDate, ?Collection $partnerIds = null): array
    {
        $fromSnaps = $this->resourceSnapshotData($fromDate, $partnerIds);
        $toSnaps = $this->resourceSnapshotData($toDate, $partnerIds);

        // Aggregate people by partner from resource records near each snapshot
        $fromImportIds = DB::table('imports')->where('type', 'resources')
            ->where('started_at', $fromDate)->pluck('id');
        $toImportIds = DB::table('imports')->where('type', 'resources')
            ->where('started_at', $toDate)->pluck('id');

        $fromPeople = DB::table('resources')->whereIn('import_id', $fromImportIds);
        $toPeople = DB::table('resources')->whereIn('import_id', $toImportIds);

        if ($partnerIds) {
            $fromPeople->whereIn('partner_id', $partnerIds);
            $toPeople->whereIn('partner_id', $partnerIds);
        }

        $fromPeopleSet = $fromPeople->select('name', 'partner_id', 'program', 'status')->get();
        $toPeopleSet = $toPeople->select('name', 'partner_id', 'program', 'status')->get();

        $fromKeys = $fromPeopleSet->map(fn ($r) => "{$r->name}|{$r->partner_id}")->unique();
        $toKeys = $toPeopleSet->map(fn ($r) => "{$r->name}|{$r->partner_id}")->unique();

        $newResourceKeys = $toKeys->diff($fromKeys);
        $departedKeys = $fromKeys->diff($toKeys);

        $newResources = $toPeopleSet->filter(fn ($r) => $newResourceKeys->contains("{$r->name}|{$r->partner_id}"))
            ->unique(fn ($r) => "{$r->name}|{$r->partner_id}");
        $departed = $fromPeopleSet->filter(fn ($r) => $departedKeys->contains("{$r->name}|{$r->partner_id}"))
            ->unique(fn ($r) => "{$r->name}|{$r->partner_id}");

        // New certifications: completed in 'to' but not in 'from'
        $fromCompleted = $fromPeopleSet->where('status', 'Completed')
            ->map(fn ($r) => "{$r->name}|{$r->program}|{$r->partner_id}")->unique();
        $toCompleted = $toPeopleSet->where('status', 'Completed')
            ->map(fn ($r) => "{$r->name}|{$r->program}|{$r->partner_id}")->unique();

        $newCertKeys = $toCompleted->diff($fromCompleted);
        $newCertifications = $toPeopleSet->filter(function ($r) use ($newCertKeys) {
            return $r->status === 'Completed' && $newCertKeys->contains("{$r->name}|{$r->program}|{$r->partner_id}");
        })->unique(fn ($r) => "{$r->name}|{$r->program}|{$r->partner_id}");

        return [
            'new_resources' => $newResources->values(),
            'departed' => $departed->values(),
            'new_certifications' => $newCertifications->values(),
        ];
    }

    /**
     * Delivery risk scoring per opportunity at a snapshot.
     * Risk based on completed delivery resources matching the LOB program.
     *
     * @return Collection<int, object>
     */
    public function deliveryRiskScoring(string $snapshotDate, ?Collection $partnerIds = null): Collection
    {
        $opps = $this->snapshotOpportunities($snapshotDate, $partnerIds);

        // Get completed delivery resources grouped by partner+program (scoped to snapshot)
        $resourceImportId = $this->resourceImportId($snapshotDate);
        $resourceQuery = DB::table('resources')
            ->where('status', 'Completed')
            ->where('role', 'Delivery')
            ->when($resourceImportId, fn ($q) => $q->where('import_id', $resourceImportId))
            ->select('partner_id', 'program', DB::raw('COUNT(DISTINCT name) as resource_count'));
        if ($partnerIds) {
            $resourceQuery->whereIn('partner_id', $partnerIds);
        }
        $resourceCounts = $resourceQuery->groupBy('partner_id', 'program')->get()
            ->groupBy('partner_id')
            ->map(fn ($items) => $items->pluck('resource_count', 'program'));

        return $opps->map(function ($opp) use ($resourceCounts) {
            $extra = is_string($opp->excel_data) ? json_decode($opp->excel_data, true) : ($opp->excel_data ?? []);
            $lob = $opp->product_area ?? '';
            $program = self::LOB_TO_PROGRAM[$lob] ?? null;

            $resourceCount = 0;
            if ($program && isset($resourceCounts[$opp->partner_id])) {
                $resourceCount = $resourceCounts[$opp->partner_id][$program] ?? 0;
            }

            $riskLevel = match (true) {
                $resourceCount === 0 => 'Very High Risk',
                $resourceCount <= 2 => 'High Risk',
                default => 'Low Risk',
            };

            $opp->resource_count = $resourceCount;
            $opp->risk_level = $riskLevel;
            $opp->matched_program = $program;

            return $opp;
        })->sortBy(function ($opp) {
            return match ($opp->risk_level) {
                'Very High Risk' => 0,
                'High Risk' => 1,
                'Low Risk' => 2,
                default => 3,
            };
        })->values();
    }

    /**
     * LOB-level narrative recommendations based on delivery risk analysis.
     *
     * @return Collection<int, array{rank: int, severity: string, title: string, why: string, impact: string, action: string}>
     */
    public function lobRecommendations(string $snapshotDate, ?Collection $partnerIds = null): Collection
    {
        $byLob = $this->deliveryRiskByLob($snapshotDate, $partnerIds);
        $scored = $this->deliveryRiskScoring($snapshotDate, $partnerIds);

        // Build per-partner breakdown grouped by LOB
        $partnersByLob = $scored->groupBy(fn ($opp) => $opp->product_area ?? 'Unknown')
            ->map(function ($opps) {
                return $opps->groupBy('partner_name')->map(function ($partnerOpps, $partnerName) {
                    $nnacv = $partnerOpps->sum('amount');
                    // Deduplicate: same partner+program should only count resources once
                    $resources = $partnerOpps->unique(fn ($o) => $o->matched_program)
                        ->sum('resource_count');
                    $gaps = $partnerOpps->where('risk_level', 'Very High Risk')->count();

                    return [
                        'partner_name' => $partnerName,
                        'partner_id' => $partnerOpps->first()->partner_id,
                        'opp_count' => $partnerOpps->count(),
                        'nnacv' => $nnacv,
                        'resource_count' => $resources,
                        'gap_count' => $gaps,
                    ];
                })->sortByDesc('nnacv')->values();
            });

        $recs = collect();
        $rank = 1;

        foreach ($byLob as $lob => $dr) {
            if ($lob === 'Unknown') {
                continue;
            }

            $program = self::LOB_TO_PROGRAM[$lob] ?? strtoupper(substr($lob, 0, 3));
            $ratio = $dr['opps'] > 0 ? $dr['resources'] / $dr['opps'] : 0;
            $staffedPct = $dr['opps'] > 0 ? round(($dr['resources'] / $dr['opps']) * 100) : 0;
            $partners = $partnersByLob->get($lob, collect());

            if ($ratio >= 1) {
                $recs->push([
                    'rank' => $rank++,
                    'type' => 'cross_train',
                    'severity' => 'success',
                    'title' => "{$dr['resources']} {$program} resources ready for cross-training from {$lob}",
                    'why' => "{$dr['resources']} resources for {$dr['opps']} opportunities (".number_format($ratio, 1).'x ratio - strong proficiency demonstrated)',
                    'impact' => 'Leverage proven talent to address gaps in other products',
                    'action' => 'Excellent candidates for certification in understaffed programs - already skilled in similar applications',
                    'partners' => $partners,
                ]);
            } else {
                $needed = $dr['gap'];
                $severity = match (true) {
                    $ratio == 0 => 'critical',
                    $ratio < 0.25 => 'critical',
                    $ratio < 0.5 => 'warning',
                    default => 'warning',
                };

                $recs->push([
                    'rank' => $rank++,
                    'type' => 'staffing_gap',
                    'severity' => $severity,
                    'title' => "{$needed} {$program} resources needed for {$lob}",
                    'why' => "{$dr['opps']} opportunities with only {$dr['resources']} resources ({$staffedPct}% staffed)",
                    'impact' => 'Could support $'.number_format($dr['nnacv_at_risk']).' in pipeline',
                    'action' => $dr['resources'] === 0
                        ? 'Immediate hire or partner engagement required'
                        : "Hire {$needed} additional {$program}-certified resources",
                    'partners' => $partners,
                ]);
            }
        }

        return $recs->sortBy(fn ($r) => match ($r['severity']) {
            'critical' => 0,
            'warning' => 1,
            default => 2,
        })->values()->map(function ($r, $i) {
            $r['rank'] = $i + 1;

            return $r;
        });
    }

    /**
     * Delivery risk aggregated by LOB: opps, resources, and gap counts.
     *
     * @return Collection<string, array{opps: int, resources: int, gap: int, nnacv_at_risk: float}>
     */
    public function deliveryRiskByLob(string $snapshotDate, ?Collection $partnerIds = null): Collection
    {
        $scored = $this->deliveryRiskScoring($snapshotDate, $partnerIds);

        return $scored->groupBy(fn ($opp) => $opp->product_area ?? 'Unknown')
            ->map(function ($items, $lob) {
                // Deduplicate resources: same partner+program combo should only count once
                $uniqueResources = $items->unique(fn ($opp) => $opp->partner_id.'|'.$opp->matched_program)
                    ->sum('resource_count');

                return [
                    'opps' => $items->count(),
                    'resources' => $uniqueResources,
                    'gap' => $items->where('risk_level', 'Very High Risk')->count(),
                    'nnacv_at_risk' => $items->where('risk_level', 'Very High Risk')->sum('amount'),
                    'opportunities' => $items->map(fn ($opp) => [
                        'name' => $opp->opportunity_name,
                        'amount' => (float) $opp->amount,
                        'partner' => $opp->partner_name,
                        'stage' => $opp->stage,
                        'resource_count' => $opp->resource_count ?? 0,
                        'risk_level' => $opp->risk_level ?? 'Unknown',
                    ])->sortByDesc('amount')->values()->all(),
                ];
            })->sortByDesc('opps');
    }

    /**
     * Resource utilization heatmap: partners x programs.
     *
     * @return array{partners: Collection, programs: array, matrix: array}
     */
    public function resourceHeatmap(?Collection $partnerIds = null, ?string $snapshotDate = null): array
    {
        $resourceImportId = $snapshotDate ? $this->resourceImportId($snapshotDate) : null;
        $query = DB::table('resources')
            ->where('status', 'Completed')
            ->where('role', 'Delivery')
            ->when($resourceImportId, fn ($q) => $q->where('resources.import_id', $resourceImportId))
            ->join('partners', 'resources.partner_id', '=', 'partners.id')
            ->select('partners.name as partner_name', 'resources.partner_id', 'resources.program', DB::raw('COUNT(DISTINCT resources.name) as count'))
            ->groupBy('partners.name', 'resources.partner_id', 'resources.program');

        if ($partnerIds) {
            $query->whereIn('resources.partner_id', $partnerIds);
        }

        $data = $query->get();

        $partners = $data->pluck('partner_name')->unique()->sort()->values();
        $matrix = [];

        foreach ($partners as $partnerName) {
            $row = [];
            foreach (self::PROGRAMS as $program) {
                $match = $data->first(fn ($d) => $d->partner_name === $partnerName && $d->program === $program);
                $row[$program] = $match ? $match->count : 0;
            }
            $matrix[$partnerName] = $row;
        }

        return [
            'partners' => $partners,
            'programs' => self::PROGRAMS,
            'matrix' => $matrix,
        ];
    }

    /**
     * Stage aging analysis: average and max days in each stage.
     *
     * @return Collection<string, array{avg_days: float, max_days: int, count: int}>
     */
    public function stageAging(string $snapshotDate, ?Collection $partnerIds = null): Collection
    {
        $opps = $this->snapshotOpportunities($snapshotDate, $partnerIds);

        return $opps->groupBy('stage')->map(function ($items, $stage) {
            $days = $items->map(function ($opp) {
                $extra = is_string($opp->excel_data) ? json_decode($opp->excel_data, true) : ($opp->excel_data ?? []);
                $stageDate = null;

                // Try to get the date this opp entered the current stage
                $stageNum = match (true) {
                    str_starts_with($opp->stage ?? '', '1.') => 'stage_1_date',
                    str_starts_with($opp->stage ?? '', '2.') => 'stage_2_date',
                    str_starts_with($opp->stage ?? '', '3.') => 'stage_3_date',
                    default => null,
                };

                if ($stageNum && ! empty($extra[$stageNum])) {
                    try {
                        $stageDate = \Carbon\Carbon::parse($extra[$stageNum]);
                    } catch (\Exception) {
                        $stageDate = null;
                    }
                }

                if (! $stageDate) {
                    // Fallback: use first_seen_at or created_at
                    $stageDate = \Carbon\Carbon::parse($opp->created_at);
                }

                return (int) $stageDate->diffInDays(now());
            })->filter();

            return [
                'avg_days' => $days->count() > 0 ? round($days->avg(), 1) : 0,
                'max_days' => $days->count() > 0 ? $days->max() : 0,
                'count' => $items->count(),
                'total_nnacv' => $items->sum('amount'),
                'opportunities' => $items->map(fn ($opp) => [
                    'name' => $opp->opportunity_name,
                    'amount' => (float) $opp->amount,
                    'partner' => $opp->partner_name,
                    'product_area' => $opp->product_area,
                    'fiscal_quarter' => $opp->fiscal_quarter,
                    'revenue_type' => $opp->revenue_type ?? null,
                ])->sortByDesc('amount')->values()->all(),
            ];
        })->sortKeys();
    }

    /**
     * Automated recommendations based on risk analysis.
     *
     * @return Collection<int, array{type: string, severity: string, message: string, partner: string, detail: string}>
     */
    public function recommendations(string $snapshotDate, ?Collection $partnerIds = null): Collection
    {
        $recs = collect();
        $riskedOpps = $this->deliveryRiskScoring($snapshotDate, $partnerIds);
        $heatmap = $this->resourceHeatmap($partnerIds, $snapshotDate);

        // 1. Partners with Very High Risk opps
        $veryHighPartners = $riskedOpps->where('risk_level', 'Very High Risk')
            ->groupBy('partner_name');

        foreach ($veryHighPartners as $partner => $opps) {
            $totalNnacv = $opps->sum('amount');
            $programs = $opps->pluck('matched_program')->unique()->filter()->implode(', ');
            $recs->push([
                'type' => 'staffing_gap',
                'severity' => 'critical',
                'message' => "No delivery resources for {$opps->count()} opportunities (\${$this->formatCurrency($totalNnacv)})",
                'partner' => $partner,
                'detail' => "Needs certified resources in: {$programs}",
            ]);
        }

        // 2. Cross-training opportunities (partner has resources in some programs but gaps in others they have opps for)
        foreach ($heatmap['matrix'] as $partnerName => $programCounts) {
            $hasPrograms = collect($programCounts)->filter(fn ($c) => $c > 0)->keys();
            $emptyPrograms = collect($programCounts)->filter(fn ($c) => $c === 0)->keys();

            if ($hasPrograms->count() > 0 && $emptyPrograms->count() > 0) {
                $partnerOpps = $riskedOpps->where('partner_name', $partnerName);
                $neededPrograms = $partnerOpps->pluck('matched_program')->unique()->filter()
                    ->intersect($emptyPrograms);

                if ($neededPrograms->isNotEmpty()) {
                    $recs->push([
                        'type' => 'cross_training',
                        'severity' => 'warning',
                        'message' => 'Has resources but missing coverage for active opportunities',
                        'partner' => $partnerName,
                        'detail' => "Cross-train into: {$neededPrograms->implode(', ')} (already has: {$hasPrograms->implode(', ')})",
                    ]);
                }
            }
        }

        // 3. Stage aging alerts (opps in early stages for > 90 days)
        $aging = $this->stageAging($snapshotDate, $partnerIds);
        foreach ($aging as $stage => $data) {
            if ($data['max_days'] > 90 && str_starts_with($stage, '1.')) {
                $recs->push([
                    'type' => 'stage_stale',
                    'severity' => 'warning',
                    'message' => 'Opportunities stalling in qualification stage',
                    'partner' => 'Portfolio',
                    'detail' => "{$data['count']} opps in \"{$stage}\" — avg {$data['avg_days']} days, max {$data['max_days']} days",
                ]);
            }
        }

        return $recs->sortBy(fn ($r) => match ($r['severity']) {
            'critical' => 0,
            'warning' => 1,
            default => 2,
        })->values();
    }

    /**
     * Partner risk cards: per-partner aggregated risk metrics.
     *
     * @return Collection<int, array>
     */
    public function partnerRiskCards(string $snapshotDate, ?Collection $partnerIds = null): Collection
    {
        $riskedOpps = $this->deliveryRiskScoring($snapshotDate, $partnerIds);
        $heatmap = $this->resourceHeatmap($partnerIds, $snapshotDate);

        return $riskedOpps->groupBy('partner_name')->map(function ($opps, $partnerName) use ($heatmap) {
            $programs = $heatmap['matrix'][$partnerName] ?? [];
            $coveredPrograms = collect($programs)->filter(fn ($c) => $c > 0)->count();
            $totalPrograms = count(self::PROGRAMS);

            return [
                'partner' => $partnerName,
                'partner_id' => $opps->first()->partner_id,
                'total_opps' => $opps->count(),
                'total_nnacv' => $opps->sum('amount'),
                'very_high_risk' => $opps->where('risk_level', 'Very High Risk')->count(),
                'high_risk' => $opps->where('risk_level', 'High Risk')->count(),
                'low_risk' => $opps->where('risk_level', 'Low Risk')->count(),
                'unique_resources' => $opps->sum('resource_count'),
                'coverage' => "{$coveredPrograms}/{$totalPrograms}",
                'coverage_pct' => $totalPrograms > 0 ? round(($coveredPrograms / $totalPrograms) * 100) : 0,
                'program_counts' => $programs,
            ];
        })->sortByDesc('total_nnacv')->values();
    }

    /**
     * LOB changes between two snapshots: per-LOB NNACV and opp deltas.
     *
     * @return Collection<string, array{from_nnacv: float, to_nnacv: float, nnacv_delta: float, from_opps: int, to_opps: int, opps_delta: int}>
     */
    public function lobChanges(string $fromDate, string $toDate, ?Collection $partnerIds = null): Collection
    {
        $fromLob = $this->dimensionBreakdown($fromDate, 'lob', $partnerIds);
        $toLob = $this->dimensionBreakdown($toDate, 'lob', $partnerIds);

        $allLobs = $fromLob->keys()->merge($toLob->keys())->unique();

        return $allLobs->mapWithKeys(function ($lob) use ($fromLob, $toLob) {
            $from = $fromLob[$lob] ?? ['nnacv' => 0, 'opps' => 0];
            $to = $toLob[$lob] ?? ['nnacv' => 0, 'opps' => 0];

            return [$lob => [
                'from_nnacv' => $from['nnacv'],
                'to_nnacv' => $to['nnacv'],
                'nnacv_delta' => $to['nnacv'] - $from['nnacv'],
                'from_opps' => $from['opps'],
                'to_opps' => $to['opps'],
                'opps_delta' => $to['opps'] - $from['opps'],
            ]];
        })->sortKeys();
    }

    /**
     * Partner summary table with WoW deltas, resources, risk, and avg aging.
     *
     * @return Collection<int, array>
     */
    public function partnerSummaryWithDeltas(string $currentDate, ?string $previousDate = null, ?Collection $partnerIds = null): Collection
    {
        $currentRisked = $this->deliveryRiskScoring($currentDate, $partnerIds);
        $previousRisked = $previousDate ? $this->deliveryRiskScoring($previousDate, $partnerIds) : null;

        // Current resource counts by partner (scoped to snapshot)
        $currentResImportId = $this->resourceImportId($currentDate);
        $resourceQuery = DB::table('resources')
            ->where('status', 'Completed')
            ->where('role', 'Delivery')
            ->when($currentResImportId, fn ($q) => $q->where('resources.import_id', $currentResImportId))
            ->join('partners', 'resources.partner_id', '=', 'partners.id')
            ->select('partners.name as partner_name', 'resources.partner_id', DB::raw('COUNT(DISTINCT resources.name) as count'))
            ->groupBy('partners.name', 'resources.partner_id');
        if ($partnerIds) {
            $resourceQuery->whereIn('resources.partner_id', $partnerIds);
        }
        $resourceByPartner = $resourceQuery->get()->pluck('count', 'partner_name');

        // Previous resource counts (scoped to snapshot)
        $prevResourceByPartner = collect();
        if ($previousDate) {
            $prevResImportId = $this->resourceImportId($previousDate);
            $prevImportIds = $prevResImportId ? collect([$prevResImportId]) : collect();
            if ($prevImportIds->isNotEmpty()) {
                $pq = DB::table('resources')
                    ->whereIn('import_id', $prevImportIds)
                    ->where('status', 'Completed')
                    ->where('role', 'Delivery')
                    ->join('partners', 'resources.partner_id', '=', 'partners.id')
                    ->select('partners.name as partner_name', DB::raw('COUNT(DISTINCT resources.name) as count'))
                    ->groupBy('partners.name');
                if ($partnerIds) {
                    $pq->whereIn('resources.partner_id', $partnerIds);
                }
                $prevResourceByPartner = $pq->get()->pluck('count', 'partner_name');
            }
        }

        // Group previous by partner for deltas
        $prevByPartner = $previousRisked ? $previousRisked->groupBy('partner_name') : collect();

        // Stage aging per partner
        $agingByPartner = $this->partnerAverageAging($currentDate, $partnerIds);

        return $currentRisked->groupBy('partner_name')->map(function ($opps, $partnerName) use ($prevByPartner, $resourceByPartner, $prevResourceByPartner, $agingByPartner) {
            $prevOpps = $prevByPartner[$partnerName] ?? collect();
            $oppsDelta = $opps->count() - $prevOpps->count();
            $nnacvDelta = $opps->sum('amount') - $prevOpps->sum('amount');
            $resources = $resourceByPartner[$partnerName] ?? 0;
            $prevResources = $prevResourceByPartner[$partnerName] ?? $resources;
            $resDelta = $resources - $prevResources;

            return [
                'partner' => $partnerName,
                'partner_id' => $opps->first()->partner_id,
                'opps' => $opps->count(),
                'opps_delta' => $oppsDelta,
                'nnacv' => $opps->sum('amount'),
                'nnacv_delta' => $nnacvDelta,
                'resources' => $resources,
                'resources_delta' => $resDelta,
                'very_high_risk' => $opps->where('risk_level', 'Very High Risk')->count(),
                'high_risk' => $opps->where('risk_level', 'High Risk')->count(),
                'avg_age' => $agingByPartner[$partnerName] ?? 0,
            ];
        })->sortByDesc('nnacv')->values();
    }

    /**
     * Partner reassignment tracking between two snapshots.
     */
    public function partnerReassignments(string $fromDate, string $toDate, ?Collection $partnerIds = null): Collection
    {
        $fromOpps = $this->snapshotOpportunities($fromDate, $partnerIds)->keyBy('opportunity_id');
        $toOpps = $this->snapshotOpportunities($toDate, $partnerIds)->keyBy('opportunity_id');

        $reassigned = collect();

        foreach ($fromOpps->keys()->intersect($toOpps->keys()) as $id) {
            $from = $fromOpps[$id];
            $to = $toOpps[$id];

            if ($from->partner_id !== $to->partner_id) {
                $reassigned->push([
                    'opportunity_name' => $to->opportunity_name,
                    'from_partner' => $from->partner_name,
                    'to_partner' => $to->partner_name,
                    'amount' => $to->amount,
                ]);
            }
        }

        return $reassigned->values();
    }

    /**
     * Average aging per partner across all their opps.
     */
    private function partnerAverageAging(string $snapshotDate, ?Collection $partnerIds = null): Collection
    {
        $opps = $this->snapshotOpportunities($snapshotDate, $partnerIds);

        return $opps->groupBy('partner_name')->map(function ($items) {
            $days = $items->map(function ($opp) {
                $extra = is_string($opp->excel_data) ? json_decode($opp->excel_data, true) : ($opp->excel_data ?? []);

                $stageKey = match (true) {
                    str_starts_with($opp->stage ?? '', '1.') => 'stage_1_date',
                    str_starts_with($opp->stage ?? '', '2.') => 'stage_2_date',
                    str_starts_with($opp->stage ?? '', '3.') => 'stage_3_date',
                    default => null,
                };

                $stageDate = null;
                if ($stageKey && ! empty($extra[$stageKey])) {
                    try {
                        $stageDate = \Carbon\Carbon::parse($extra[$stageKey]);
                    } catch (\Exception) {
                    }
                }
                if (! $stageDate) {
                    $stageDate = \Carbon\Carbon::parse($opp->created_at);
                }

                return (int) $stageDate->diffInDays(now());
            });

            return $days->count() > 0 ? (int) round($days->avg()) : 0;
        });
    }

    /**
     * Calculate average sales velocity (days from first_seen_at to close_date) for closed won opps.
     *
     * @return array{avg_days: int, min_days: int, max_days: int}
     */
    private function calculateVelocity(Collection $closedWonOpps, string $snapshotDate): array
    {
        $days = $closedWonOpps
            ->filter(fn ($o) => $o->close_date)
            ->map(function ($opp) {
                $extra = is_string($opp->excel_data) ? json_decode($opp->excel_data, true) : ($opp->excel_data ?? []);
                $startDate = ! empty($extra['stage_1_date'])
                    ? \Carbon\Carbon::parse($extra['stage_1_date'])
                    : ($opp->first_seen_at ? \Carbon\Carbon::parse($opp->first_seen_at) : null);

                if (! $startDate) {
                    return null;
                }

                $closed = \Carbon\Carbon::parse($opp->close_date);

                return max(0, (int) $startDate->diffInDays($closed));
            })
            ->filter(fn ($v) => $v !== null);

        if ($days->isEmpty()) {
            return ['avg_days' => 0, 'min_days' => 0, 'max_days' => 0];
        }

        return [
            'avg_days' => (int) round($days->avg()),
            'min_days' => (int) $days->min(),
            'max_days' => (int) $days->max(),
        ];
    }

    /**
     * Get snapshot opportunities with partner names joined.
     */
    private function snapshotOpportunities(string $snapshotDate, ?Collection $partnerIds = null): Collection
    {
        $query = DB::table('opportunity_snapshots as os')
            ->join('partners as p', 'os.partner_id', '=', 'p.id')
            ->leftJoin('opportunities as o', 'os.opportunity_id', '=', 'o.id')
            ->where('os.snapshot_date', $snapshotDate)
            ->select(
                'os.*',
                'p.name as partner_name',
                'o.excel_data',
                'o.first_seen_at',
            );

        if ($partnerIds) {
            $query->whereIn('os.partner_id', $partnerIds);
        }

        if ($this->fiscalYear) {
            $query->where('os.fiscal_year', $this->fiscalYear);
        }

        return $query->get();
    }

    /**
     * Get resource snapshot data for a date.
     */
    private function resourceSnapshotData(string $date, ?Collection $partnerIds = null): Collection
    {
        $query = DB::table('resource_snapshots')
            ->where('snapshot_date', $date);

        if ($partnerIds) {
            $query->whereIn('partner_id', $partnerIds);
        }

        return $query->get();
    }

    /**
     * Get distinct filter option values for the filter modal.
     *
     * @return array{
     *     geos: array<string>,
     *     regions: array<string>,
     *     lobs: array<string>,
     *     stages: array<string>,
     *     quarters: array<string>,
     *     approaches: array<string>,
     *     geoRegionMap: array<string, array<string>>,
     *     psmPartnerMap: array<int, array<array{id: int, name: string}>>,
     * }
     */
    public function filterOptions(): array
    {
        $opps = DB::table('opportunity_snapshots as os')
            ->join('opportunities as o', 'os.opportunity_id', '=', 'o.id')
            ->select('os.stage', 'os.fiscal_quarter', 'os.product_area', 'os.partner_id', 'o.excel_data')
            ->get();

        $geos = collect();
        $regions = collect();
        $approaches = collect();
        $geoRegionMap = [];

        foreach ($opps as $opp) {
            $extra = is_string($opp->excel_data) ? json_decode($opp->excel_data, true) : ($opp->excel_data ?? []);

            $geo = $extra['geo'] ?? null;
            $region = $extra['region'] ?? null;
            $approach = $extra['implementation_approach'] ?? null;

            if ($geo) {
                $geos->push($geo);
                if ($region) {
                    $geoRegionMap[$geo] = array_unique(array_merge($geoRegionMap[$geo] ?? [], [$region]));
                }
            }
            if ($region) {
                $regions->push($region);
            }
            if ($approach) {
                $approaches->push($approach);
            }
        }

        // Sort regions within each geo
        foreach ($geoRegionMap as &$regs) {
            sort($regs);
        }

        // PSM → Partner mapping
        $psmPartnerMap = [];
        $psmPartners = DB::table('partners')
            ->join('users', 'partners.user_id', '=', 'users.id')
            ->select('partners.id', 'partners.name', 'partners.user_id')
            ->orderBy('partners.name')
            ->get();

        foreach ($psmPartners as $p) {
            $psmPartnerMap[$p->user_id][] = ['id' => $p->id, 'name' => $p->name];
        }

        return [
            'geos' => $geos->unique()->sort()->values()->all(),
            'regions' => $regions->unique()->sort()->values()->all(),
            'lobs' => $opps->pluck('product_area')->filter()->unique()->sort()->values()->all(),
            'stages' => $opps->pluck('stage')->filter()->unique()->sort()->values()->all(),
            'quarters' => $opps->pluck('fiscal_quarter')->filter()->unique()->sort()->values()->all(),
            'approaches' => $approaches->unique()->sort()->values()->all(),
            'geoRegionMap' => $geoRegionMap,
            'psmPartnerMap' => $psmPartnerMap,
        ];
    }

    private function formatCurrency(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return number_format($amount / 1_000_000, 1).'M';
        }

        return number_format($amount / 1_000, 0).'K';
    }
}
