<?php

namespace App\Mcp\Tools;

use App\Models\StrategyPlan;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Query strategy plan data — targets, actuals, variance, assumptions, budgets, and initiatives. Answers questions like "How are we tracking against the FY27 plan?" or "What are our target metrics?"')]
class StrategyPlanTool extends Tool
{
    public function handle(Request $request): Response
    {
        $query = StrategyPlan::with(['targets', 'assumptions', 'budgets', 'initiatives']);

        if ($planId = $request->get('plan_id')) {
            $plan = $query->findOrFail($planId);

            return $this->formatPlan($plan);
        }

        if ($fiscalYear = $request->get('fiscal_year')) {
            $query->where('fiscal_year', $fiscalYear);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'active');
        }

        $plans = $query->orderByDesc('fiscal_year')->limit(5)->get();

        if ($plans->count() === 1) {
            return $this->formatPlan($plans->first());
        }

        return Response::json([
            'plans' => $plans->map(fn (StrategyPlan $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'fiscal_year' => $p->fiscal_year,
                'status' => $p->status,
                'target_count' => $p->targets->count(),
                'total_budget' => $p->total_budget,
                'total_budget_actual' => $p->total_budget_actual,
            ])->all(),
        ]);
    }

    protected function formatPlan(StrategyPlan $plan): Response
    {
        $targets = $plan->targets->map(fn ($t) => [
            'category' => $t->category,
            'metric' => $t->metric_label,
            'metric_key' => $t->metric_key,
            'unit' => $t->unit,
            'target' => (float) $t->target_value,
            'actual' => (float) ($t->actual_value ?? 0),
            'attainment_pct' => $t->getAttainmentPercentage(),
            'variance_pct' => $t->getVariancePercentage(),
            'formatted_target' => $t->formatted_target,
            'formatted_actual' => $t->formatted_actual,
            'quarterly' => [
                'q1' => ['target' => (float) ($t->q1_target ?? 0), 'actual' => (float) ($t->q1_actual ?? 0)],
                'q2' => ['target' => (float) ($t->q2_target ?? 0), 'actual' => (float) ($t->q2_actual ?? 0)],
                'q3' => ['target' => (float) ($t->q3_target ?? 0), 'actual' => (float) ($t->q3_actual ?? 0)],
                'q4' => ['target' => (float) ($t->q4_target ?? 0), 'actual' => (float) ($t->q4_actual ?? 0)],
            ],
        ]);

        $assumptions = $plan->assumptions->map(fn ($a) => [
            'key' => $a->assumption_key,
            'label' => $a->label,
            'planned' => (float) $a->planned_value,
            'actual' => (float) ($a->actual_value ?? 0),
            'unit' => $a->unit,
            'variance_pct' => $a->getVariancePercentage(),
            'notes' => $a->notes,
        ]);

        $budgets = $plan->budgets->map(fn ($b) => [
            'category' => $b->category,
            'line_item' => $b->line_item,
            'planned' => (float) $b->planned_amount,
            'actual' => (float) ($b->actual_amount ?? 0),
            'variance' => (float) ($b->planned_amount - ($b->actual_amount ?? 0)),
        ]);

        $initiatives = $plan->initiatives->map(fn ($i) => [
            'name' => $i->name,
            'category' => $i->category,
            'status' => $i->status,
            'priority' => $i->priority,
            'progress_pct' => $i->progress_percentage ?? 0,
            'owner' => $i->owner,
        ]);

        $overallAttainment = $targets->count() > 0
            ? round($targets->avg('attainment_pct'), 1)
            : 0;

        return Response::json([
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'fiscal_year' => $plan->fiscal_year,
                'status' => $plan->status,
                'period_type' => $plan->period_type,
            ],
            'summary' => [
                'overall_attainment_pct' => $overallAttainment,
                'targets_on_track' => $targets->filter(fn ($t) => $t['attainment_pct'] >= 90)->count(),
                'targets_at_risk' => $targets->filter(fn ($t) => $t['attainment_pct'] < 70 && $t['attainment_pct'] > 0)->count(),
                'total_budget_planned' => $plan->total_budget,
                'total_budget_actual' => $plan->total_budget_actual,
                'budget_utilization_pct' => $plan->total_budget > 0
                    ? round(($plan->total_budget_actual / $plan->total_budget) * 100, 1)
                    : 0,
                'initiatives_completed' => $initiatives->where('status', 'completed')->count(),
                'initiatives_total' => $initiatives->count(),
            ],
            'targets' => $targets->all(),
            'assumptions' => $assumptions->all(),
            'budgets' => $budgets->all(),
            'initiatives' => $initiatives->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'plan_id' => $schema->integer()->description('Specific plan ID to retrieve'),
            'fiscal_year' => $schema->string()->description('Filter by fiscal year (e.g. "FY27")'),
            'status' => $schema->string()->description('Plan status filter (default: active). Options: draft, committed, active, closed'),
        ];
    }
}
