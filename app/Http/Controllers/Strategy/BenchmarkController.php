<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\SignalAggregateService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BenchmarkController extends Controller
{
    public function __invoke(Request $request, SignalAggregateService $aggregateService): View
    {
        // External cohort benchmarks (received from control plane via agent)
        $benchmarkRaw = AppSetting::get('signal_engine_benchmarks');
        $benchmark = $benchmarkRaw ? json_decode($benchmarkRaw, true) : null;

        // Our own portfolio aggregate (internal data)
        $aggregate = $aggregateService->buildAggregate();

        // Signal sharing status
        $sharingEnabled = (bool) AppSetting::get('signal_sharing_enabled', '0');

        return view('strategy.benchmarks', compact('benchmark', 'aggregate', 'sharingEnabled'));
    }
}
