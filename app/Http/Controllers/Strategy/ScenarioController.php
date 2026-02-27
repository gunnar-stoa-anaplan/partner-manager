<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\StrategyPlan;
use App\Models\StrategyScenario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function index(): View
    {
        $scenarios = StrategyScenario::query()
            ->with(['basePlan', 'creator'])
            ->withCount('overrides')
            ->latest()
            ->get();

        $plans = StrategyPlan::whereIn('status', ['active', 'committed'])->get();

        return view('strategy.scenarios.index', compact('scenarios', 'plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_plan_id' => 'required|exists:strategy_plans,id',
        ]);

        $scenario = StrategyScenario::create([
            ...$request->only('name', 'description', 'base_plan_id'),
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        return redirect()->route('strategy.scenarios.show', $scenario)
            ->with('success', 'Scenario created successfully.');
    }

    public function show(StrategyScenario $scenario): View
    {
        $scenario->load([
            'basePlan.targets',
            'basePlan.assumptions',
            'basePlan.budgets',
            'overrides',
            'creator',
        ]);

        // Build projected values by applying overrides to base plan data
        $projectedTargets = $scenario->basePlan->targets->map(function ($target) use ($scenario) {
            $override = $scenario->overrides
                ->where('override_type', 'target')
                ->where('reference_id', $target->id)
                ->first();

            return [
                'id' => $target->id,
                'metric_label' => $target->metric_label,
                'category' => $target->category,
                'unit' => $target->unit,
                'base_value' => $target->target_value,
                'projected_value' => $override ? $override->override_value : $target->target_value,
                'has_override' => (bool) $override,
                'delta' => $override ? $override->override_value - $target->target_value : 0,
            ];
        });

        $projectedAssumptions = $scenario->basePlan->assumptions->map(function ($assumption) use ($scenario) {
            $override = $scenario->overrides
                ->where('override_type', 'assumption')
                ->where('reference_id', $assumption->id)
                ->first();

            return [
                'id' => $assumption->id,
                'label' => $assumption->label,
                'unit' => $assumption->unit,
                'base_value' => $assumption->planned_value,
                'projected_value' => $override ? $override->override_value : $assumption->planned_value,
                'has_override' => (bool) $override,
            ];
        });

        $projectedBudgets = $scenario->basePlan->budgets->map(function ($budget) use ($scenario) {
            $override = $scenario->overrides
                ->where('override_type', 'budget')
                ->where('reference_id', $budget->id)
                ->first();

            return [
                'id' => $budget->id,
                'label' => $budget->label,
                'category' => $budget->category,
                'base_value' => $budget->planned_amount,
                'projected_value' => $override ? $override->override_value : $budget->planned_amount,
                'has_override' => (bool) $override,
                'delta' => $override ? $override->override_value - $budget->planned_amount : 0,
            ];
        });

        return view('strategy.scenarios.show', compact(
            'scenario',
            'projectedTargets',
            'projectedAssumptions',
            'projectedBudgets',
        ));
    }

    public function storeOverride(Request $request, StrategyScenario $scenario): RedirectResponse
    {
        if ($scenario->isFinalized()) {
            return redirect()->route('strategy.scenarios.show', $scenario)
                ->with('error', 'Finalized scenarios cannot be modified.');
        }

        $request->validate([
            'override_type' => 'required|in:target,assumption,budget',
            'reference_id' => 'required|integer',
            'original_value' => 'required|numeric',
            'override_value' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        // Remove existing override for the same reference
        $scenario->overrides()
            ->where('override_type', $request->override_type)
            ->where('reference_id', $request->reference_id)
            ->delete();

        $scenario->overrides()->create($request->only([
            'override_type', 'reference_id', 'original_value', 'override_value', 'notes',
        ]));

        return redirect()->route('strategy.scenarios.show', $scenario)
            ->with('success', 'Override applied successfully.');
    }

    public function compare(Request $request): View
    {
        $scenarioIds = $request->input('scenarios', []);

        $scenarios = StrategyScenario::query()
            ->with(['basePlan.targets', 'basePlan.assumptions', 'basePlan.budgets', 'overrides'])
            ->whereIn('id', $scenarioIds)
            ->get();

        $allScenarios = StrategyScenario::with('basePlan')->get();

        // Build comparison matrix
        $comparisonTargets = collect();
        if ($scenarios->isNotEmpty()) {
            $basePlan = $scenarios->first()->basePlan;
            $basePlan->load('targets');

            $comparisonTargets = $basePlan->targets->map(function ($target) use ($scenarios) {
                $row = [
                    'metric_label' => $target->metric_label,
                    'category' => $target->category,
                    'unit' => $target->unit,
                    'base_value' => $target->target_value,
                    'scenarios' => [],
                ];

                foreach ($scenarios as $scenario) {
                    $override = $scenario->overrides
                        ->where('override_type', 'target')
                        ->where('reference_id', $target->id)
                        ->first();

                    $row['scenarios'][$scenario->id] = [
                        'value' => $override ? $override->override_value : $target->target_value,
                        'has_override' => (bool) $override,
                    ];
                }

                return $row;
            });
        }

        return view('strategy.scenarios.compare', compact(
            'scenarios',
            'allScenarios',
            'comparisonTargets',
        ));
    }

    public function finalize(StrategyScenario $scenario): RedirectResponse
    {
        $scenario->update(['status' => 'finalized']);

        return redirect()->route('strategy.scenarios.show', $scenario)
            ->with('success', 'Scenario finalized.');
    }
}
