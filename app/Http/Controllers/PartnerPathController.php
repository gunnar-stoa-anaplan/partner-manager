<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerPathStage;
use App\Models\PartnerScorecard;
use App\Models\PartnerStageHistory;
use App\Models\PartnerTask;
use App\Models\User;
use App\Services\GateEvaluationService;
use App\Services\TaskAutoPopulationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PartnerPathController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Role-based partner scoping — manager and admin see all partners
        $canSeeAll = in_array($user->role, ['admin', 'manager']);
        $partnerIds = $canSeeAll
            ? Partner::pluck('id')
            : Partner::where('user_id', $user->id)->pluck('id');

        $partners = Partner::whereIn('id', $partnerIds)->orderBy('name')->get();

        // PSM filter (manager/admin only)
        $selectedPsmId = $canSeeAll ? $request->get('psm_id') : null;
        $psmUsers = $canSeeAll
            ? User::where('role', 'psm')->orderBy('name')->get(['id', 'name'])
            : collect();

        // Optional partner filter
        $selectedPartnerId = $request->get('partner_id');
        $filteredPartnerIds = $partnerIds;

        if ($selectedPsmId) {
            $psmPartnerIds = Partner::where('user_id', $selectedPsmId)->pluck('id');
            $filteredPartnerIds = $filteredPartnerIds->intersect($psmPartnerIds);
        }
        if ($selectedPartnerId) {
            $filteredPartnerIds = $filteredPartnerIds->intersect([$selectedPartnerId]);
        }

        // Narrow partner dropdown when PSM filter is active
        $partnersForDropdown = $selectedPsmId
            ? $partners->where('user_id', (int) $selectedPsmId)->values()
            : $partners;

        $paths = PartnerPath::where('is_active', true)->with(['stages' => fn($q) => $q->orderBy('sort_order'), 'gates'])->get();
        $defaultPath = $paths->first();

        $enrollments = PartnerPathEnrollment::with(['partner.user', 'currentStage', 'path'])
            ->where('status', 'active')
            ->whereIn('partner_id', $filteredPartnerIds)
            ->get();

        $stageGroups = $enrollments->groupBy(fn($e) => $e->current_stage_id);

        // Stage duration analytics
        $stageDurations = $this->computeStageDurations($defaultPath, $filteredPartnerIds);

        // Chart data for ApexCharts
        $stages = $defaultPath ? $defaultPath->stages->sortBy('sort_order') : collect();
        $chartData = $stages->map(fn($stage) => [
            'stage' => $stage->name,
            'color' => $stage->color,
            'avg_days' => $stageDurations[$stage->id]['avg'] ?? 0,
            'median_days' => $stageDurations[$stage->id]['median'] ?? 0,
            'partners' => $stageGroups->get($stage->id, collect())->count(),
        ])->values();

        // Latest scorecard scores per partner
        $scoreValues = [];
        foreach ($filteredPartnerIds as $pid) {
            $score = PartnerScorecard::where('partner_id', $pid)
                ->orderByDesc('scored_at')
                ->value('overall_score');
            if ($score !== null) {
                $scoreValues[$pid] = $score;
            }
        }

        // Management metrics (manager/admin only)
        $managementMetrics = $canSeeAll
            ? $this->computeManagementMetrics($enrollments, $stageDurations, $scoreValues, $filteredPartnerIds, $stages)
            : null;

        return view('plan.paths.index', compact(
            'paths', 'enrollments', 'stageGroups', 'partners', 'partnersForDropdown',
            'selectedPartnerId', 'selectedPsmId', 'stageDurations', 'chartData',
            'scoreValues', 'canSeeAll', 'psmUsers', 'managementMetrics'
        ));
    }

    /**
     * Compute per-stage duration statistics from stage history.
     */
    private function computeStageDurations(?PartnerPath $path, Collection $partnerIds): array
    {
        if (!$path) return [];

        $stages = $path->stages->sortBy('sort_order');

        // Get all history for this path
        $allHistory = PartnerStageHistory::where('partner_path_id', $path->id)
            ->whereIn('partner_id', $partnerIds)
            ->orderBy('partner_id')
            ->orderBy('transitioned_at')
            ->get()
            ->groupBy('partner_id');

        // Get current enrollments for "still in stage" partners
        $currentEnrollments = PartnerPathEnrollment::where('partner_path_id', $path->id)
            ->whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->get()
            ->keyBy('partner_id');

        $stageDays = []; // stage_id => [array of day counts]

        foreach ($allHistory as $partnerId => $history) {
            $entries = $history->values();

            for ($i = 0; $i < $entries->count(); $i++) {
                $entry = $entries[$i];
                $stageId = $entry->to_stage_id;
                $enteredAt = $entry->transitioned_at;

                // Find when they left this stage (next transition from this stage)
                $leftAt = null;
                for ($j = $i + 1; $j < $entries->count(); $j++) {
                    if ($entries[$j]->from_stage_id === $stageId) {
                        $leftAt = $entries[$j]->transitioned_at;
                        break;
                    }
                }

                // If still in this stage, use now()
                if (!$leftAt) {
                    $enrollment = $currentEnrollments->get($partnerId);
                    if ($enrollment && $enrollment->current_stage_id === $stageId) {
                        $leftAt = now();
                    } else {
                        continue; // Skip — partner left this stage but we can't find when
                    }
                }

                $days = (int) $enteredAt->diffInDays($leftAt);
                $stageDays[$stageId][] = $days;
            }
        }

        // Compute statistics per stage
        $result = [];
        foreach ($stages as $stage) {
            $days = $stageDays[$stage->id] ?? [];
            if (empty($days)) {
                $result[$stage->id] = ['avg' => 0, 'median' => 0, 'min' => 0, 'max' => 0, 'count' => 0];
                continue;
            }

            sort($days);
            $count = count($days);
            $mid = intdiv($count, 2);
            $median = $count % 2 === 0 ? ($days[$mid - 1] + $days[$mid]) / 2 : $days[$mid];

            $result[$stage->id] = [
                'avg' => round(array_sum($days) / $count),
                'median' => round($median),
                'min' => min($days),
                'max' => max($days),
                'count' => $count,
            ];
        }

        return $result;
    }

    /**
     * Compute management-level metrics (admin/manager only).
     */
    private function computeManagementMetrics(
        Collection $enrollments,
        array $stageDurations,
        array $scoreValues,
        Collection $filteredPartnerIds,
        Collection $stages
    ): array {
        // PSM Breakdown: group enrollments by PSM
        $psmBreakdown = $enrollments->groupBy(fn($e) => $e->partner->user_id)
            ->map(function ($group, $userId) {
                $psmName = $group->first()->partner->user?->name ?? 'Unassigned';
                return [
                    'psm_name' => $psmName,
                    'psm_id' => $userId,
                    'total' => $group->count(),
                    'by_stage' => $group->groupBy(fn($e) => $e->currentStage->name)
                        ->map->count()
                        ->toArray(),
                ];
            })->values();

        // At-Risk Partners: stalled (>1.5x avg) or low score (<30)
        $atRiskPartners = $enrollments->filter(function ($e) use ($stageDurations, $scoreValues) {
            $days = (int) $e->stage_entered_at->diffInDays(now());
            $stats = $stageDurations[$e->current_stage_id] ?? null;
            $isStalled = $stats && $stats['avg'] > 0 && $days > ($stats['avg'] * 1.5);
            $score = $scoreValues[$e->partner_id] ?? null;
            $isLowScore = $score !== null && $score < 30;
            return $isStalled || $isLowScore;
        })->map(function ($e) use ($stageDurations, $scoreValues) {
            $days = (int) $e->stage_entered_at->diffInDays(now());
            $stats = $stageDurations[$e->current_stage_id] ?? null;
            $score = $scoreValues[$e->partner_id] ?? null;
            $reasons = [];
            if ($stats && $stats['avg'] > 0 && $days > ($stats['avg'] * 1.5)) {
                $reasons[] = 'Stalled (' . $days . 'd vs ' . $stats['avg'] . 'd avg)';
            }
            if ($score !== null && $score < 30) {
                $reasons[] = 'Low score (' . number_format($score, 0) . ')';
            }
            return [
                'enrollment' => $e,
                'days_in_stage' => $days,
                'score' => $score,
                'reasons' => $reasons,
                'psm_name' => $e->partner->user?->name ?? 'Unassigned',
            ];
        })->values();

        // Task Completion Rate per stage (required tasks only)
        $taskStats = PartnerTask::whereIn('partner_id', $filteredPartnerIds)
            ->where('requirement_level', 'required')
            ->whereNotNull('stage_id')
            ->selectRaw('stage_id, status, COUNT(*) as cnt')
            ->groupBy('stage_id', 'status')
            ->get()
            ->groupBy('stage_id')
            ->map(function ($group) {
                $total = $group->sum('cnt');
                $completed = $group->where('status', 'completed')->sum('cnt');
                return [
                    'total' => $total,
                    'completed' => $completed,
                    'rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                ];
            });

        // Average enrollment age
        $avgEnrollmentAge = $enrollments->isNotEmpty()
            ? round($enrollments->avg(fn($e) => $e->enrolled_at->diffInDays(now())))
            : 0;

        return [
            'psmBreakdown' => $psmBreakdown,
            'atRiskPartners' => $atRiskPartners,
            'taskStats' => $taskStats,
            'avgEnrollmentAge' => $avgEnrollmentAge,
        ];
    }

    public function show(PartnerPathEnrollment $enrollment, GateEvaluationService $gateService)
    {
        $enrollment->load(['partner', 'currentStage', 'path.stages', 'path.gates']);

        $nextStage = $enrollment->path->stages
            ->where('stage_number', '>', $enrollment->currentStage->stage_number)
            ->sortBy('stage_number')
            ->first();

        $gateResults = $nextStage
            ? $gateService->evaluateGates($enrollment->partner, $enrollment->current_stage_id, $nextStage->id)
            : collect();

        $history = $enrollment->partner->stageHistory()
            ->with(['fromStage', 'toStage', 'approver'])
            ->orderByDesc('transitioned_at')
            ->get();

        return view('plan.paths.show', compact('enrollment', 'nextStage', 'gateResults', 'history'));
    }

    public function advanceStage(Request $request, PartnerPathEnrollment $enrollment, GateEvaluationService $gateService, TaskAutoPopulationService $taskService)
    {
        $nextStage = PartnerPathStage::where('partner_path_id', $enrollment->partner_path_id)
            ->where('stage_number', '>', $enrollment->currentStage->stage_number)
            ->orderBy('stage_number')
            ->firstOrFail();

        $gateResults = $gateService->evaluateGates($enrollment->partner, $enrollment->current_stage_id, $nextStage->id);

        $enrollment->partner->stageHistory()->create([
            'partner_path_id' => $enrollment->partner_path_id,
            'from_stage_id' => $enrollment->current_stage_id,
            'to_stage_id' => $nextStage->id,
            'transitioned_at' => now(),
            'approved_by' => auth()->id(),
            'gate_results' => $gateResults->toArray(),
            'notes' => $request->input('notes'),
        ]);

        $enrollment->update([
            'current_stage_id' => $nextStage->id,
            'stage_entered_at' => now(),
            'last_reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $taskService->populateTasksForEnrollment($enrollment->fresh());

        return redirect()->route('plan.paths.show', $enrollment)
            ->with('success', "Partner advanced to {$nextStage->name} stage.");
    }

    public function enroll(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'partner_path_id' => 'required|exists:partner_paths,id',
        ]);

        $path = PartnerPath::with('stages')->findOrFail($request->partner_path_id);
        $firstStage = $path->stages->sortBy('stage_number')->first();

        $enrollment = PartnerPathEnrollment::create([
            'partner_id' => $request->partner_id,
            'partner_path_id' => $path->id,
            'current_stage_id' => $firstStage->id,
            'enrolled_at' => now(),
            'stage_entered_at' => now(),
            'status' => 'active',
        ]);

        app(TaskAutoPopulationService::class)->populateTasksForEnrollment($enrollment);

        return redirect()->route('plan.paths.index')
            ->with('success', 'Partner enrolled in path successfully.');
    }
}
