<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Partner;
use App\Models\PartnerAssignedGoal;
use App\Models\PartnerHealthSnapshot;
use App\Models\PartnerTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChatContextService
{
    /**
     * Build partner portfolio context for the given user.
     *
     * @return array{user_name: string, partner_count: int, partners: array, pipeline_summary: array, health_scores: array, goals: array, upcoming_tasks: array, certifications_summary: array}
     */
    public function buildContext(User $user): array
    {
        $partnerQuery = Partner::without(['projects', 'contacts']);

        if ($user->role !== 'admin') {
            $partnerQuery->where('user_id', $user->id);
        }

        $partnerIds = $partnerQuery->pluck('id')->toArray();
        $partners = Partner::without(['projects', 'contacts'])
            ->whereIn('id', $partnerIds)
            ->select('id', 'name', 'geography', 'region')
            ->limit(50)
            ->get();

        return [
            'user_name' => $user->name,
            'partner_count' => count($partnerIds),
            'partners' => $partners->map(fn (Partner $p) => [
                'name' => $p->name,
                'geography' => $p->geography,
                'region' => $p->region,
            ])->toArray(),
            'pipeline_summary' => $this->buildPipelineSummary($partnerIds),
            'health_scores' => $this->buildHealthScores($partnerIds),
            'goals' => $this->buildGoalsSummary($partnerIds),
            'upcoming_tasks' => $this->buildTasksSummary($partnerIds),
            'certifications_summary' => $this->buildCertificationsSummary($partnerIds),
        ];
    }

    /**
     * @param  array<int>  $partnerIds
     * @return array{total_amount: float, count: int, by_stage: array}
     */
    protected function buildPipelineSummary(array $partnerIds): array
    {
        if (empty($partnerIds)) {
            return ['total_amount' => 0, 'count' => 0, 'by_stage' => []];
        }

        $opportunities = DB::table('opportunities')
            ->whereIn('partner_id', $partnerIds)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->select('stage', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('stage')
            ->get();

        return [
            'total_amount' => $opportunities->sum('total'),
            'count' => $opportunities->sum('count'),
            'by_stage' => $opportunities->map(fn ($row) => [
                'stage' => $row->stage,
                'amount' => $row->total,
                'count' => $row->count,
            ])->toArray(),
        ];
    }

    /**
     * @param  array<int>  $partnerIds
     * @return array<array{partner: string, score: float, risk_level: string, signals: array}>
     */
    protected function buildHealthScores(array $partnerIds): array
    {
        if (empty($partnerIds)) {
            return [];
        }

        return PartnerHealthSnapshot::whereIn('partner_id', $partnerIds)
            ->whereIn('id', function ($query) use ($partnerIds) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('partner_health_snapshots')
                    ->whereIn('partner_id', $partnerIds)
                    ->groupBy('partner_id');
            })
            ->with('partner:id,name')
            ->orderBy('score')
            ->limit(20)
            ->get()
            ->map(fn ($snapshot) => [
                'partner' => $snapshot->partner->name ?? 'Unknown',
                'score' => (float) $snapshot->score,
                'risk_level' => $snapshot->risk_level,
                'signals' => $snapshot->signals ?? [],
            ])
            ->toArray();
    }

    /**
     * @param  array<int>  $partnerIds
     * @return array{fiscal_year: string, goals: array}
     */
    protected function buildGoalsSummary(array $partnerIds): array
    {
        $fyStart = (int) AppSetting::get('fiscal_year_start_month', 2);
        $now = now();
        $year = $now->month >= $fyStart ? $now->year : $now->year - 1;
        $fiscalYear = 'FY'.substr($year + 1, 2);

        if (empty($partnerIds)) {
            return ['fiscal_year' => $fiscalYear, 'goals' => []];
        }

        $goals = PartnerAssignedGoal::whereIn('partner_id', $partnerIds)
            ->where('fiscal_year', $fiscalYear)
            ->where('target_value', '>', 0)
            ->with('partner:id,name')
            ->get();

        return [
            'fiscal_year' => $fiscalYear,
            'goals' => $goals->map(fn ($g) => [
                'partner' => $g->partner->name ?? 'Unknown',
                'name' => $g->name,
                'target' => $g->target_value,
                'actual' => $g->actual_value,
                'attainment' => $g->getAttainmentPercentage(),
                'status' => $g->status,
            ])->toArray(),
        ];
    }

    /**
     * @param  array<int>  $partnerIds
     * @return array{overdue: array, upcoming: array}
     */
    protected function buildTasksSummary(array $partnerIds): array
    {
        if (empty($partnerIds)) {
            return ['overdue' => [], 'upcoming' => []];
        }

        $overdue = PartnerTask::whereIn('partner_id', $partnerIds)
            ->overdue()
            ->with('partner:id,name')
            ->orderBy('due_date')
            ->limit(20)
            ->get()
            ->map(fn ($t) => [
                'partner' => $t->partner->name ?? 'Unknown',
                'title' => $t->title,
                'due_date' => $t->due_date?->toDateString(),
                'priority' => $t->priority,
            ])->toArray();

        $upcoming = PartnerTask::whereIn('partner_id', $partnerIds)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'waived')
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(30))
            ->with('partner:id,name')
            ->orderBy('due_date')
            ->limit(20)
            ->get()
            ->map(fn ($t) => [
                'partner' => $t->partner->name ?? 'Unknown',
                'title' => $t->title,
                'due_date' => $t->due_date?->toDateString(),
                'priority' => $t->priority,
            ])->toArray();

        return ['overdue' => $overdue, 'upcoming' => $upcoming];
    }

    /**
     * @param  array<int>  $partnerIds
     * @return array{active: int, expiring_soon: int, by_type: array}
     */
    protected function buildCertificationsSummary(array $partnerIds): array
    {
        if (empty($partnerIds)) {
            return ['active' => 0, 'expiring_soon' => 0, 'by_type' => []];
        }

        $active = DB::table('certifications')
            ->whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->count();

        $expiringSoon = DB::table('certifications')
            ->whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays(60)])
            ->count();

        $byType = DB::table('certifications')
            ->whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'type' => $row->type,
                'count' => $row->count,
            ])->toArray();

        return [
            'active' => $active,
            'expiring_soon' => $expiringSoon,
            'by_type' => $byType,
        ];
    }
}
