<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\StrategyPlan;
use App\Services\StrategyActualsService;
use Illuminate\Http\RedirectResponse;

class ActualsController extends Controller
{
    public function refresh(StrategyPlan $plan, StrategyActualsService $service): RedirectResponse
    {
        $count = $service->refreshPlan($plan);

        return redirect()->route('strategy.plans.variance', $plan)
            ->with('success', "Refreshed actuals for {$count} targets.");
    }
}
