<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\StrategyPlan;
use App\Models\StrategyPlanReview;
use App\Models\StrategyScenario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $activePlan = StrategyPlan::query()
            ->whereIn('status', ['active', 'committed'])
            ->with(['targets', 'budgets', 'initiatives'])
            ->latest('committed_at')
            ->first();

        $plans = StrategyPlan::query()
            ->withCount(['targets', 'scenarios', 'reviews'])
            ->latest()
            ->limit(5)
            ->get();

        $scenarioCount = StrategyScenario::count();

        $upcomingReviews = StrategyPlanReview::query()
            ->with('plan')
            ->where('status', 'scheduled')
            ->where('review_date', '>=', today())
            ->orderBy('review_date')
            ->limit(5)
            ->get();

        $stats = [
            'total_plans' => StrategyPlan::count(),
            'active_plans' => StrategyPlan::whereIn('status', ['active', 'committed'])->count(),
            'draft_plans' => StrategyPlan::where('status', 'draft')->count(),
            'total_scenarios' => $scenarioCount,
            'pending_reviews' => StrategyPlanReview::where('status', 'scheduled')->count(),
        ];

        // Compute variance summary for active plan
        $varianceSummary = null;
        if ($activePlan) {
            $targets = $activePlan->targets;
            $onTrack = $targets->filter(fn ($t) => $t->actual_value !== null && $t->getAttainmentPercentage() >= 70)->count();
            $atRisk = $targets->filter(fn ($t) => $t->actual_value !== null && $t->getAttainmentPercentage() >= 40 && $t->getAttainmentPercentage() < 70)->count();
            $behind = $targets->filter(fn ($t) => $t->actual_value !== null && $t->getAttainmentPercentage() < 40)->count();
            $noData = $targets->filter(fn ($t) => $t->actual_value === null)->count();

            $totalBudget = $activePlan->budgets->sum('planned_amount');
            $actualBudget = $activePlan->budgets->sum('actual_amount');

            $varianceSummary = compact('onTrack', 'atRisk', 'behind', 'noData', 'totalBudget', 'actualBudget');
        }

        return view('strategy.dashboard', compact(
            'activePlan',
            'plans',
            'stats',
            'upcomingReviews',
            'varianceSummary',
        ));
    }
}
