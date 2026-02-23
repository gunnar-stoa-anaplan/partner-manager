<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\StrategyPlan;
use App\Models\StrategyPlanInitiative;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $plans = StrategyPlan::query()
            ->with('creator')
            ->withCount(['targets', 'assumptions', 'budgets', 'initiatives', 'scenarios', 'reviews'])
            ->latest()
            ->get();

        return view('strategy.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('strategy.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fiscal_year' => 'required|string|max:10',
            'period_type' => 'required|in:annual,quarterly',
            'quarter' => 'nullable|integer|min:1|max:4',
            'description' => 'nullable|string',
        ]);

        $plan = StrategyPlan::create([
            ...$request->only('name', 'fiscal_year', 'period_type', 'quarter', 'description'),
            'status' => 'draft',
            'version' => 1,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('strategy.plans.show', $plan)
            ->with('success', 'Strategic plan created successfully.');
    }

    public function show(StrategyPlan $plan): View
    {
        $plan->load([
            'targets',
            'assumptions',
            'budgets',
            'initiatives.owner',
            'reviews.reviewer',
            'scenarios',
            'creator',
            'committedByUser',
            'parentPlan',
            'childVersions',
        ]);

        $coeUsers = User::whereIn('role', ['admin', 'coe'])->orderBy('name')->get();

        return view('strategy.plans.show', compact('plan', 'coeUsers'));
    }

    public function update(Request $request, StrategyPlan $plan): RedirectResponse
    {
        if (! $plan->isEditable()) {
            return redirect()->route('strategy.plans.show', $plan)
                ->with('error', 'Committed plans cannot be edited. Create a new version instead.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $plan->update($request->only('name', 'description'));

        return redirect()->route('strategy.plans.show', $plan)
            ->with('success', 'Plan updated successfully.');
    }

    public function commit(StrategyPlan $plan): RedirectResponse
    {
        if (! $plan->isEditable()) {
            return redirect()->route('strategy.plans.show', $plan)
                ->with('error', 'This plan is already committed.');
        }

        $plan->update([
            'status' => 'active',
            'committed_at' => now(),
            'committed_by' => auth()->id(),
        ]);

        return redirect()->route('strategy.plans.show', $plan)
            ->with('success', 'Plan committed and activated. Targets are now locked.');
    }

    public function variance(StrategyPlan $plan): View
    {
        $plan->load(['targets', 'assumptions', 'budgets']);

        $targetsByCategory = $plan->targets->groupBy('category');

        return view('strategy.plans.variance', compact('plan', 'targetsByCategory'));
    }

    public function createVersion(StrategyPlan $plan): RedirectResponse
    {
        if (! $plan->isCommitted()) {
            return redirect()->route('strategy.plans.show', $plan)
                ->with('error', 'Only committed plans can be versioned.');
        }

        $newPlan = $plan->replicate(['committed_at', 'committed_by']);
        $newPlan->status = 'draft';
        $newPlan->parent_plan_id = $plan->id;
        $newPlan->version = $plan->version + 1;
        $newPlan->name = $plan->name.' (v'.($plan->version + 1).')';
        $newPlan->created_by = auth()->id();
        $newPlan->save();

        // Clone child rows
        foreach ($plan->targets as $target) {
            $newPlan->targets()->create($target->only([
                'category', 'metric_key', 'metric_label', 'unit',
                'target_value', 'q1_target', 'q2_target', 'q3_target', 'q4_target',
            ]));
        }

        foreach ($plan->assumptions as $assumption) {
            $newPlan->assumptions()->create($assumption->only([
                'assumption_key', 'label', 'planned_value', 'unit', 'notes',
            ]));
        }

        foreach ($plan->budgets as $budget) {
            $newPlan->budgets()->create($budget->only([
                'category', 'label', 'planned_amount',
                'q1_planned', 'q2_planned', 'q3_planned', 'q4_planned', 'notes',
            ]));
        }

        foreach ($plan->initiatives as $initiative) {
            $newPlan->initiatives()->create($initiative->only([
                'title', 'description', 'category', 'owner_id',
                'target_start_date', 'target_end_date', 'expected_impact',
            ]));
        }

        return redirect()->route('strategy.plans.show', $newPlan)
            ->with('success', 'New version created from '.$plan->name.'.');
    }

    public function storeTarget(Request $request, StrategyPlan $plan): RedirectResponse
    {
        if (! $plan->isEditable()) {
            return redirect()->route('strategy.plans.show', $plan)
                ->with('error', 'Cannot add targets to a committed plan.');
        }

        $request->validate([
            'category' => 'required|string',
            'metric_key' => 'required|string',
            'metric_label' => 'required|string|max:255',
            'unit' => 'required|in:currency,count,percentage',
            'target_value' => 'required|numeric|min:0',
            'q1_target' => 'nullable|numeric|min:0',
            'q2_target' => 'nullable|numeric|min:0',
            'q3_target' => 'nullable|numeric|min:0',
            'q4_target' => 'nullable|numeric|min:0',
        ]);

        $plan->targets()->create($request->only([
            'category', 'metric_key', 'metric_label', 'unit',
            'target_value', 'q1_target', 'q2_target', 'q3_target', 'q4_target',
        ]));

        return redirect()->route('strategy.plans.show', $plan)
            ->with('success', 'Target added successfully.');
    }

    public function storeAssumption(Request $request, StrategyPlan $plan): RedirectResponse
    {
        if (! $plan->isEditable()) {
            return redirect()->route('strategy.plans.show', $plan)
                ->with('error', 'Cannot add assumptions to a committed plan.');
        }

        $request->validate([
            'assumption_key' => 'required|string',
            'label' => 'required|string|max:255',
            'planned_value' => 'required|numeric',
            'unit' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $plan->assumptions()->create($request->only([
            'assumption_key', 'label', 'planned_value', 'unit', 'notes',
        ]));

        return redirect()->route('strategy.plans.show', $plan)
            ->with('success', 'Assumption added successfully.');
    }

    public function storeBudget(Request $request, StrategyPlan $plan): RedirectResponse
    {
        if (! $plan->isEditable()) {
            return redirect()->route('strategy.plans.show', $plan)
                ->with('error', 'Cannot add budget items to a committed plan.');
        }

        $request->validate([
            'category' => 'required|string',
            'label' => 'required|string|max:255',
            'planned_amount' => 'required|numeric|min:0',
            'q1_planned' => 'nullable|numeric|min:0',
            'q2_planned' => 'nullable|numeric|min:0',
            'q3_planned' => 'nullable|numeric|min:0',
            'q4_planned' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $plan->budgets()->create($request->only([
            'category', 'label', 'planned_amount',
            'q1_planned', 'q2_planned', 'q3_planned', 'q4_planned', 'notes',
        ]));

        return redirect()->route('strategy.plans.show', $plan)
            ->with('success', 'Budget line item added successfully.');
    }

    public function storeInitiative(Request $request, StrategyPlan $plan): RedirectResponse
    {
        if (! $plan->isEditable()) {
            return redirect()->route('strategy.plans.show', $plan)
                ->with('error', 'Cannot add initiatives to a committed plan.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'owner_id' => 'nullable|exists:users,id',
            'target_start_date' => 'nullable|date',
            'target_end_date' => 'nullable|date|after_or_equal:target_start_date',
            'expected_impact' => 'nullable|string',
        ]);

        $plan->initiatives()->create($request->only([
            'title', 'description', 'category', 'owner_id',
            'target_start_date', 'target_end_date', 'expected_impact',
        ]));

        return redirect()->route('strategy.plans.show', $plan)
            ->with('success', 'Initiative added successfully.');
    }

    public function updateInitiative(Request $request, StrategyPlanInitiative $initiative): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
        ]);

        $initiative->update($request->only('status', 'actual_start_date', 'actual_end_date'));

        return redirect()->route('strategy.plans.show', $initiative->strategy_plan_id)
            ->with('success', 'Initiative updated successfully.');
    }
}
