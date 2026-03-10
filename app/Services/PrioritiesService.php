<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use App\Models\PsmObjective;
use App\Models\User;
use App\Models\WeeklyFocus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PrioritiesService
{
    public function __construct(
        protected ObjectiveComputationService $computationService
    ) {}

    /**
     * Portfolio summary: partners with health data, signals, active objective counts.
     *
     * @return Collection<int, array{partner: Partner, snapshot: ?PartnerHealthSnapshot, objectives_count: int, avg_progress: float}>
     */
    public function portfolioSummary(User $user): Collection
    {
        $partners = $user->psmPartners()
            ->with(['healthSnapshots' => fn ($q) => $q->latest('snapshot_date')->limit(1)])
            ->get();

        return $partners->map(function (Partner $partner) use ($user) {
            $snapshot = $partner->healthSnapshots->first();

            $objectives = PsmObjective::where('user_id', $user->id)
                ->where('partner_id', $partner->id)
                ->where('status', 'active')
                ->get();

            $avgProgress = $objectives->count() > 0
                ? $objectives->avg(fn (PsmObjective $o) => $o->progressPercentage())
                : 0;

            return [
                'partner' => $partner,
                'snapshot' => $snapshot,
                'objectives_count' => $objectives->count(),
                'avg_progress' => round($avgProgress, 1),
            ];
        })->sortBy(fn ($item) => $item['snapshot']?->score ?? 0);
    }

    /**
     * Generate suggested objectives from partner health signals.
     *
     * @return array{urgent: array, recommendations: array}
     */
    public function suggestedObjectives(User $user): array
    {
        $urgent = [];
        $recommendations = [];

        $partners = $user->psmPartners()
            ->with(['healthSnapshots' => fn ($q) => $q->latest('snapshot_date')->limit(1)])
            ->get();

        foreach ($partners as $partner) {
            $snapshot = $partner->healthSnapshots->first();
            if (! $snapshot) {
                continue;
            }

            $signals = $snapshot->signals ?? [];
            $dimensions = $snapshot->dimensions ?? [];
            $isUrgent = in_array($snapshot->risk_level, ['critical', 'at_risk']);

            foreach ($signals as $signal) {
                $suggestion = $this->mapSignalToSuggestion($signal, $partner, $dimensions);
                if (! $suggestion) {
                    continue;
                }

                // Skip if user already has an active objective for this partner + metric_type
                $exists = PsmObjective::where('user_id', $user->id)
                    ->where('partner_id', $partner->id)
                    ->where('metric_type', $suggestion['metric_type'])
                    ->where('status', 'active')
                    ->exists();

                if ($exists) {
                    continue;
                }

                $suggestion['partner'] = $partner;
                $suggestion['signal'] = $signal;

                if ($isUrgent) {
                    $urgent[] = $suggestion;
                } else {
                    $recommendations[] = $suggestion;
                }
            }
        }

        return [
            'urgent' => $urgent,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get or create a weekly focus for a given week.
     */
    public function getOrCreateWeeklyFocus(User $user, ?Carbon $weekStart = null): WeeklyFocus
    {
        $weekStart = ($weekStart ?? WeeklyFocus::currentWeekStart())->startOfWeek(Carbon::MONDAY);

        return WeeklyFocus::firstOrCreate(
            ['user_id' => $user->id, 'week_start' => $weekStart->toDateString()],
            ['summary' => null]
        );
    }

    /**
     * Get the weekly view data: active objectives with computed actuals + action items.
     *
     * @return array{focus: WeeklyFocus, objectives: Collection, items: Collection}
     */
    public function weeklyView(User $user, ?Carbon $weekStart = null): array
    {
        $focus = $this->getOrCreateWeeklyFocus($user, $weekStart);

        $objectives = PsmObjective::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('partner')
            ->orderBy('target_date')
            ->get();

        // Compute current values for non-manual objectives
        $objectives->each(function (PsmObjective $objective) {
            if ($objective->metric_type !== 'manual') {
                $this->computationService->computeAndUpdate($objective);
            }
        });

        $items = $focus->items()->with('partner')->get();

        return [
            'focus' => $focus,
            'objectives' => $objectives,
            'items' => $items,
        ];
    }

    /**
     * Last week's review data: completed/progressed objectives + action item outcomes.
     *
     * @return array{focus: ?WeeklyFocus, objectives_completed: Collection, objectives_progressed: Collection, items: Collection}
     */
    public function lastWeekReview(User $user): array
    {
        $lastWeekStart = WeeklyFocus::currentWeekStart()->subWeek();

        $focus = WeeklyFocus::forUser($user->id)
            ->forWeek($lastWeekStart)
            ->with('items.partner')
            ->first();

        $objectivesCompleted = PsmObjective::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $lastWeekStart)
            ->where('completed_at', '<', $lastWeekStart->copy()->addWeek())
            ->with('partner')
            ->get();

        $objectivesProgressed = PsmObjective::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('partner')
            ->get()
            ->filter(fn (PsmObjective $o) => $o->progressPercentage() > 0);

        return [
            'focus' => $focus,
            'objectives_completed' => $objectivesCompleted,
            'objectives_progressed' => $objectivesProgressed,
            'items' => $focus?->items ?? collect(),
        ];
    }

    /**
     * Copy incomplete action items from last week to current week.
     */
    public function carryOverActionItems(User $user): int
    {
        $lastWeekStart = WeeklyFocus::currentWeekStart()->subWeek();

        $lastFocus = WeeklyFocus::forUser($user->id)
            ->forWeek($lastWeekStart)
            ->first();

        if (! $lastFocus) {
            return 0;
        }

        $currentFocus = $this->getOrCreateWeeklyFocus($user);

        $incompleteItems = $lastFocus->items()
            ->whereIn('status', ['planned', 'in_progress'])
            ->get();

        $maxSort = $currentFocus->items()->max('sort_order') ?? -1;

        $count = 0;
        foreach ($incompleteItems as $item) {
            $currentFocus->items()->create([
                'partner_id' => $item->partner_id,
                'description' => $item->description,
                'status' => 'planned',
                'sort_order' => ++$maxSort,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Team overview: direct reports with objective stats and portfolio health.
     *
     * @return Collection<int, array{user: User, partners_count: int, objectives_active: int, objectives_completed: int, items_total: int, items_done: int, avg_health: float, at_risk_count: int, last_review: ?string}>
     */
    public function teamOverview(User $manager): Collection
    {
        $reports = $manager->directReports()->with('role')->get();

        return $reports->map(function (User $report) {
            $partners = $report->psmPartners;
            $partnerIds = $partners->pluck('id');

            $activeObjectives = PsmObjective::where('user_id', $report->id)
                ->where('status', 'active')
                ->count();

            $completedObjectives = PsmObjective::where('user_id', $report->id)
                ->where('status', 'completed')
                ->count();

            $currentFocus = WeeklyFocus::forUser($report->id)
                ->forWeek(WeeklyFocus::currentWeekStart())
                ->with('items')
                ->first();

            $itemsTotal = $currentFocus?->items->count() ?? 0;
            $itemsDone = $currentFocus?->items->where('status', 'completed')->count() ?? 0;

            $snapshots = PartnerHealthSnapshot::whereIn('partner_id', $partnerIds)
                ->where('snapshot_date', '>=', now()->subDays(7))
                ->latest('snapshot_date')
                ->get()
                ->unique('partner_id');

            $avgHealth = $snapshots->count() > 0 ? round($snapshots->avg('score'), 1) : 0;
            $atRisk = $snapshots->whereIn('risk_level', ['at_risk', 'critical'])->count();

            $lastReview = WeeklyFocus::where('user_id', $report->id)
                ->whereNotNull('reviewed_at')
                ->latest('reviewed_at')
                ->value('reviewed_at');

            return [
                'user' => $report,
                'partners_count' => $partners->count(),
                'objectives_active' => $activeObjectives,
                'objectives_completed' => $completedObjectives,
                'items_total' => $itemsTotal,
                'items_done' => $itemsDone,
                'avg_health' => $avgHealth,
                'at_risk_count' => $atRisk,
                'last_review' => $lastReview,
            ];
        });
    }

    /**
     * Timeline data: weekly progress over the past N weeks.
     *
     * @return Collection<int, array{week_start: string, objectives_completed: int, items_completed: int, avg_health: ?float}>
     */
    public function timeline(User $user, int $weeks = 8): Collection
    {
        $data = collect();
        $currentWeek = WeeklyFocus::currentWeekStart();

        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $currentWeek->copy()->subWeeks($i);
            $weekEnd = $weekStart->copy()->addWeek();

            $objectivesCompleted = PsmObjective::where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('completed_at', '>=', $weekStart)
                ->where('completed_at', '<', $weekEnd)
                ->count();

            $focus = WeeklyFocus::forUser($user->id)->forWeek($weekStart)->first();
            $itemsCompleted = $focus
                ? $focus->items()->where('status', 'completed')->count()
                : 0;

            $data->push([
                'week_start' => $weekStart->toDateString(),
                'objectives_completed' => $objectivesCompleted,
                'items_completed' => $itemsCompleted,
            ]);
        }

        return $data->reverse()->values();
    }

    /**
     * Map a health signal string to a suggested objective template.
     *
     * @return ?array{title: string, metric_type: string, metric_config: ?array, category: string, suggested_target: int}
     */
    protected function mapSignalToSuggestion(string $signal, Partner $partner, array $dimensions): ?array
    {
        $signalLower = strtolower($signal);

        if (str_contains($signalLower, 'enablement') || str_contains($signalLower, 'blueprint')) {
            return [
                'title' => "Increase blueprint completions for {$partner->name}",
                'metric_type' => 'blueprint_completions',
                'metric_config' => null,
                'category' => 'enablement',
                'suggested_target' => 5,
            ];
        }

        if (str_contains($signalLower, 'stale') || str_contains($signalLower, 'deal')) {
            return [
                'title' => "Review and advance stale opportunities for {$partner->name}",
                'metric_type' => 'opportunity_count',
                'metric_config' => null,
                'category' => 'pipeline',
                'suggested_target' => 3,
            ];
        }

        if (str_contains($signalLower, 'certification') || str_contains($signalLower, 'expiring')) {
            return [
                'title' => "Renew expiring certifications for {$partner->name}",
                'metric_type' => 'certifications',
                'metric_config' => null,
                'category' => 'certification',
                'suggested_target' => 3,
            ];
        }

        if (str_contains($signalLower, 'pipeline declined') || str_contains($signalLower, 'pipeline')) {
            return [
                'title' => "Build pipeline for {$partner->name}",
                'metric_type' => 'pipeline_value',
                'metric_config' => null,
                'category' => 'pipeline',
                'suggested_target' => 100000,
            ];
        }

        if (str_contains($signalLower, 'no contact') || str_contains($signalLower, 'engagement')) {
            return [
                'title' => "Re-engage {$partner->name} with enablement sessions",
                'metric_type' => 'enablement_sessions',
                'metric_config' => null,
                'category' => 'enablement',
                'suggested_target' => 2,
            ];
        }

        if (str_contains($signalLower, 'delivery') || str_contains($signalLower, 'csat')) {
            return [
                'title' => "Improve delivery outcomes for {$partner->name}",
                'metric_type' => 'manual',
                'metric_config' => null,
                'category' => 'delivery',
                'suggested_target' => 1,
            ];
        }

        if (str_contains($signalLower, 'goal attainment')) {
            return [
                'title' => "Improve goal attainment for {$partner->name}",
                'metric_type' => 'manual',
                'metric_config' => null,
                'category' => 'general',
                'suggested_target' => 1,
            ];
        }

        return null;
    }
}
