<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewWeeklyFocusRequest;
use App\Http\Requests\StoreActionItemRequest;
use App\Http\Requests\StoreObjectiveRequest;
use App\Http\Requests\UpdateActionItemRequest;
use App\Models\PsmObjective;
use App\Models\User;
use App\Models\WeeklyFocus;
use App\Models\WeeklyFocusItem;
use App\Services\ObjectiveComputationService;
use App\Services\PrioritiesService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrioritiesController extends Controller
{
    public function __construct(
        protected PrioritiesService $prioritiesService,
        protected ObjectiveComputationService $computationService
    ) {}

    /**
     * Portfolio Priorities — partners ranked by urgency with suggestions.
     */
    public function index(): View
    {
        $user = auth()->user();
        $portfolio = $this->prioritiesService->portfolioSummary($user);
        $suggestions = $this->prioritiesService->suggestedObjectives($user);

        $totalPartners = $portfolio->count();
        $atRiskCount = $portfolio->filter(fn ($p) => in_array($p['snapshot']?->risk_level, ['at_risk', 'critical']))->count();
        $activeObjectives = PsmObjective::where('user_id', $user->id)->where('status', 'active')->count();
        $avgProgress = $portfolio->avg('avg_progress');

        return view('priorities.index', compact(
            'portfolio',
            'suggestions',
            'totalPartners',
            'atRiskCount',
            'activeObjectives',
            'avgProgress',
        ));
    }

    /**
     * Weekly Focus — the main working view for objectives + action items.
     */
    public function weeklyFocus(Request $request): View
    {
        $user = auth()->user();
        $weekStart = $request->has('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : null;

        $weeklyData = $this->prioritiesService->weeklyView($user, $weekStart);
        $lastWeek = $this->prioritiesService->lastWeekReview($user);
        $partners = $user->psmPartners()->orderBy('name')->get();

        return view('priorities.weekly', array_merge($weeklyData, [
            'lastWeek' => $lastWeek,
            'partners' => $partners,
        ]));
    }

    /**
     * Progress Timeline — week-over-week charts.
     */
    public function timeline(): View
    {
        $user = auth()->user();
        $timelineData = $this->prioritiesService->timeline($user);

        return view('priorities.timeline', compact('timelineData'));
    }

    /**
     * Team Overview — manager view of direct reports.
     */
    public function team(): View
    {
        $user = auth()->user();

        if ($user->directReports()->count() === 0) {
            abort(403, 'You do not have direct reports.');
        }

        $teamData = $this->prioritiesService->teamOverview($user);

        return view('priorities.team', compact('teamData'));
    }

    /**
     * Team Member Drill-Down — manager views a specific PSM.
     */
    public function teamMember(User $user): View
    {
        $manager = auth()->user();

        if ($user->manager_id !== $manager->id && ! $manager->bypassesPermissions()) {
            abort(403, 'You can only view your direct reports.');
        }

        $weeklyData = $this->prioritiesService->weeklyView($user);
        $lastWeek = $this->prioritiesService->lastWeekReview($user);

        return view('priorities.team-member', array_merge($weeklyData, [
            'member' => $user,
            'lastWeek' => $lastWeek,
        ]));
    }

    /**
     * Store a new metric-tracked objective.
     */
    public function storeObjective(StoreObjectiveRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        $objective = PsmObjective::create([
            'user_id' => $user->id,
            'partner_id' => $data['partner_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'metric_type' => $data['metric_type'],
            'metric_config' => $data['metric_config'] ?? null,
            'target_value' => $data['target_value'],
            'baseline_value' => 0,
            'current_value' => 0,
            'target_date' => $data['target_date'],
            'status' => 'active',
            'source_signal' => $data['source_signal'] ?? null,
        ]);

        // Compute baseline (current actual at time of creation)
        if ($objective->metric_type !== 'manual') {
            $baseline = $this->computationService->compute($objective);
            $objective->update([
                'baseline_value' => $baseline,
                'current_value' => $baseline,
            ]);
        }

        $redirect = $request->input('_redirect');

        return redirect()->to($redirect ?: route('priorities.weekly'))
            ->with('success', 'Objective created successfully.');
    }

    /**
     * Update an existing objective (status change or cancel).
     */
    public function updateObjective(Request $request, PsmObjective $objective): RedirectResponse
    {
        $user = auth()->user();

        if ($objective->user_id !== $user->id && ! $user->bypassesPermissions()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:active,completed,missed,cancelled'],
            'current_value' => ['sometimes', 'numeric', 'min:0'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'category' => ['sometimes', 'string', 'in:delivery,pipeline,enablement,certification,general'],
            'metric_type' => ['sometimes', 'string', 'in:resources_registered,certifications,pipeline_value,opportunity_count,projects_delivered,manual'],
            'target_value' => ['sometimes', 'numeric', 'min:0.01'],
            'target_date' => ['sometimes', 'date', 'after:today'],
            'partner_id' => ['sometimes', 'exists:partners,id'],
            'priority' => ['sometimes', 'integer', 'min:0'],
        ]);

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $objective->update($validated);

        return redirect()->back()->with('success', 'Objective updated.');
    }

    /**
     * Reorder objectives via AJAX (drag-and-drop).
     */
    public function reorderObjectives(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:psm_objectives,id'],
        ]);

        $user = auth()->user();

        foreach ($validated['ids'] as $index => $id) {
            PsmObjective::where('id', $id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                    if ($user->bypassesPermissions()) {
                        $q->orWhereRaw('1=1');
                    }
                })
                ->update(['priority' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete an objective.
     */
    public function destroyObjective(PsmObjective $objective): RedirectResponse
    {
        $user = auth()->user();

        if ($objective->user_id !== $user->id && ! $user->bypassesPermissions()) {
            abort(403);
        }

        $objective->delete();

        return redirect()->back()->with('success', 'Objective deleted.');
    }

    /**
     * Store a new action item on the current week's focus.
     */
    public function storeActionItem(StoreActionItemRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $focus = $this->prioritiesService->getOrCreateWeeklyFocus($user);
        $data = $request->validated();

        $maxSort = $focus->items()->max('sort_order') ?? -1;

        $focus->items()->create([
            'partner_id' => $data['partner_id'],
            'description' => $data['description'],
            'status' => 'planned',
            'sort_order' => $maxSort + 1,
        ]);

        $redirect = $request->input('_redirect');

        return redirect()->to($redirect ?: route('priorities.weekly'))
            ->with('success', 'Action item added.');
    }

    /**
     * Update an action item's status.
     */
    public function updateActionItem(UpdateActionItemRequest $request, WeeklyFocusItem $item): RedirectResponse
    {
        $user = auth()->user();

        if ($item->weeklyFocus->user_id !== $user->id && ! $user->bypassesPermissions()) {
            abort(403);
        }

        $data = $request->validated();

        $item->update([
            'status' => $data['status'],
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);

        return redirect()->back()->with('success', 'Action item updated.');
    }

    /**
     * Delete an action item.
     */
    public function destroyActionItem(WeeklyFocusItem $item): RedirectResponse
    {
        $user = auth()->user();

        if ($item->weeklyFocus->user_id !== $user->id && ! $user->bypassesPermissions()) {
            abort(403);
        }

        $item->delete();

        return redirect()->back()->with('success', 'Action item removed.');
    }

    /**
     * Carry over incomplete action items from last week.
     */
    public function carryOver(): RedirectResponse
    {
        $user = auth()->user();
        $count = $this->prioritiesService->carryOverActionItems($user);

        return redirect()->route('priorities.weekly')
            ->with('success', "{$count} item(s) carried over from last week.");
    }

    /**
     * Manager reviews a PSM's weekly focus.
     */
    public function review(ReviewWeeklyFocusRequest $request, WeeklyFocus $focus): RedirectResponse
    {
        $manager = auth()->user();

        $focusUser = $focus->user;
        if ($focusUser->manager_id !== $manager->id && ! $manager->bypassesPermissions()) {
            abort(403, 'You can only review your direct reports.');
        }

        $focus->update([
            'manager_notes' => $request->validated('manager_notes'),
            'reviewed_at' => now(),
            'reviewed_by' => $manager->id,
        ]);

        return redirect()->back()->with('success', 'Weekly focus reviewed.');
    }

    /**
     * Manager suggests an objective for a PSM.
     */
    public function suggestObjective(StoreObjectiveRequest $request, User $user): RedirectResponse
    {
        $manager = auth()->user();

        if ($user->manager_id !== $manager->id && ! $manager->bypassesPermissions()) {
            abort(403, 'You can only suggest objectives for your direct reports.');
        }

        $data = $request->validated();

        $objective = PsmObjective::create([
            'user_id' => $user->id,
            'partner_id' => $data['partner_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'metric_type' => $data['metric_type'],
            'metric_config' => $data['metric_config'] ?? null,
            'target_value' => $data['target_value'],
            'baseline_value' => 0,
            'current_value' => 0,
            'target_date' => $data['target_date'],
            'status' => 'active',
            'suggested_by' => $manager->id,
            'source_signal' => $data['source_signal'] ?? null,
        ]);

        if ($objective->metric_type !== 'manual') {
            $baseline = $this->computationService->compute($objective);
            $objective->update([
                'baseline_value' => $baseline,
                'current_value' => $baseline,
            ]);
        }

        return redirect()->route('priorities.team.member', $user)
            ->with('success', "Objective suggested for {$user->name}.");
    }

    /**
     * Update weekly focus summary.
     */
    public function updateSummary(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'summary' => ['nullable', 'string', 'max:2000'],
        ]);

        $focus = $this->prioritiesService->getOrCreateWeeklyFocus($user);
        $focus->update(['summary' => $validated['summary']]);

        return redirect()->route('priorities.weekly')
            ->with('success', 'Summary updated.');
    }
}
