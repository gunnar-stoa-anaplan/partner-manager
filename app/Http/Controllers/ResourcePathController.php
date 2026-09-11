<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\ResourcePath;
use App\Models\ResourcePathEnrollment;
use App\Models\ResourcePathStage;
use App\Models\ResourceStageHistory;
use App\Models\User;
use App\Services\ResourceGateEvaluationService;
use App\Services\ResourceTaskAutoPopulationService;
use App\Support\VisibilityContext;
use Illuminate\Http\Request;

class ResourcePathController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Partner visibility is enforced by the row-level scope (ScopedToActor
        // on Partner): this list already contains only the actor's authorized
        // partners — owner + assignments + reporting hierarchy, or all when the
        // actor holds unrestricted visibility. No hard-coded role logic.
        $visibleIds = app(VisibilityContext::class)->visiblePartnerIds();
        $partners = Partner::orderBy('name')->get();
        $partnerIds = $partners->pluck('id');

        // Filters — the PSM filter is an oversight convenience for actors who
        // can see beyond their own book: unrestricted actors, or managers.
        $canSeeAll = $visibleIds === null || $user->directReports()->exists();
        $selectedPsmId = $canSeeAll ? $request->get('psm_id') : null;
        $selectedPartnerId = $request->get('partner_id');
        $selectedPathId = $request->get('path_id');

        $psmUsers = $canSeeAll
            ? User::whereRelation('role', 'slug', 'psm')->orderBy('name')->get(['id', 'name'])
            : collect();

        $filteredPartnerIds = $partnerIds;
        if ($selectedPsmId) {
            $psmPartnerIds = Partner::where('user_id', $selectedPsmId)->pluck('id');
            $filteredPartnerIds = $filteredPartnerIds->intersect($psmPartnerIds);
        }
        if ($selectedPartnerId) {
            $filteredPartnerIds = $filteredPartnerIds->intersect([$selectedPartnerId]);
        }

        $partnersForDropdown = $selectedPsmId
            ? $partners->where('user_id', (int) $selectedPsmId)->values()
            : $partners;

        $paths = ResourcePath::where('is_active', true)
            ->with(['stages' => fn ($q) => $q->orderBy('sort_order')])
            ->get();

        $enrollmentQuery = ResourcePathEnrollment::with(['contact', 'partner.user', 'currentStage', 'path'])
            ->where('status', 'active')
            ->whereIn('partner_id', $filteredPartnerIds);

        if ($selectedPathId) {
            $enrollmentQuery->where('resource_path_id', $selectedPathId);
        }

        $enrollments = $enrollmentQuery->get();

        // Group by path, then by stage
        $enrollmentsByPath = $enrollments->groupBy('resource_path_id');

        // Chart data: stage distribution across all paths
        $stageGroups = $enrollments->groupBy('current_stage_id');
        $chartData = collect();
        foreach ($paths as $path) {
            foreach ($path->stages as $stage) {
                $chartData->push([
                    'path' => $path->name,
                    'stage' => $stage->name,
                    'color' => $stage->color,
                    'count' => $stageGroups->get($stage->id, collect())->count(),
                ]);
            }
        }

        // Summary KPIs
        $totalEnrolled = $enrollments->count();
        $byPathCounts = $enrollments->groupBy(fn ($e) => $e->path->name)->map->count();
        $avgDaysInStage = $enrollments->isNotEmpty()
            ? round($enrollments->avg(fn ($e) => $e->stage_entered_at->diffInDays(now())))
            : 0;

        // Funnel data: for each path, count resources at or beyond each stage
        $funnelData = collect();
        foreach ($paths as $path) {
            $pathEnrollments = $enrollmentsByPath->get($path->id, collect());
            if ($pathEnrollments->isEmpty()) {
                continue;
            }

            $stages = $path->stages->sortBy('sort_order')->values();
            $funnelStages = [];

            foreach ($stages as $stage) {
                // Count resources currently at or past this stage
                $atOrBeyond = $pathEnrollments->filter(
                    fn ($e) => $e->currentStage->stage_number >= $stage->stage_number
                )->count();

                $funnelStages[] = [
                    'name' => $stage->name,
                    'color' => $stage->color,
                    'count' => $atOrBeyond,
                ];
            }

            $funnelData->put($path->id, [
                'path_name' => $path->name,
                'total' => $pathEnrollments->count(),
                'stages' => $funnelStages,
            ]);
        }

        return view('plan.resource-paths.index', compact(
            'paths', 'enrollments', 'enrollmentsByPath', 'stageGroups', 'chartData',
            'partners', 'partnersForDropdown', 'psmUsers', 'canSeeAll',
            'selectedPsmId', 'selectedPartnerId', 'selectedPathId',
            'totalEnrolled', 'byPathCounts', 'avgDaysInStage', 'funnelData'
        ));
    }

    public function show(ResourcePathEnrollment $enrollment, ResourceGateEvaluationService $gateService)
    {
        $enrollment->load(['contact', 'partner', 'currentStage', 'path.stages', 'path.gates']);

        $nextStage = $enrollment->path->stages
            ->where('stage_number', '>', $enrollment->currentStage->stage_number)
            ->sortBy('stage_number')
            ->first();

        $gateResults = $nextStage
            ? $gateService->evaluateGates($enrollment->contact, $enrollment->current_stage_id, $nextStage->id)
            : collect();

        $history = ResourceStageHistory::where('contact_id', $enrollment->contact_id)
            ->where('resource_path_id', $enrollment->resource_path_id)
            ->with(['fromStage', 'toStage', 'approver'])
            ->orderByDesc('transitioned_at')
            ->get();

        return view('plan.resource-paths.show', compact('enrollment', 'nextStage', 'gateResults', 'history'));
    }

    public function enroll(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'partner_id' => 'required|exists:partners,id',
            'resource_path_id' => 'required|exists:resource_paths,id',
        ]);

        $path = ResourcePath::with('stages')->findOrFail($request->resource_path_id);
        $firstStage = $path->stages->sortBy('stage_number')->first();

        $enrollment = ResourcePathEnrollment::create([
            'contact_id' => $request->contact_id,
            'partner_id' => $request->partner_id,
            'resource_path_id' => $path->id,
            'current_stage_id' => $firstStage->id,
            'enrolled_at' => now(),
            'stage_entered_at' => now(),
            'status' => 'active',
        ]);

        app(ResourceTaskAutoPopulationService::class)->populateTasksForEnrollment($enrollment);

        return redirect()->route('plan.resource-paths.index')
            ->with('success', 'Resource enrolled in path successfully.');
    }

    public function advanceStage(
        Request $request,
        ResourcePathEnrollment $enrollment,
        ResourceGateEvaluationService $gateService,
        ResourceTaskAutoPopulationService $taskService,
    ) {
        $nextStage = ResourcePathStage::where('resource_path_id', $enrollment->resource_path_id)
            ->where('stage_number', '>', $enrollment->currentStage->stage_number)
            ->orderBy('stage_number')
            ->firstOrFail();

        $gateResults = $gateService->evaluateGates($enrollment->contact, $enrollment->current_stage_id, $nextStage->id);

        ResourceStageHistory::create([
            'contact_id' => $enrollment->contact_id,
            'partner_id' => $enrollment->partner_id,
            'resource_path_id' => $enrollment->resource_path_id,
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

        return redirect()->route('plan.resource-paths.show', $enrollment)
            ->with('success', "Resource advanced to {$nextStage->name} stage.");
    }
}
