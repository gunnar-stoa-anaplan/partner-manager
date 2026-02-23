<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\ActivationAggregateService;
use Illuminate\Contracts\View\View;

class ActivationBenchmarkController extends Controller
{
    public function __invoke(ActivationAggregateService $aggregateService): View
    {
        $aggregate = $aggregateService->buildAggregate();
        $benchmarkData = json_decode(AppSetting::get('activation_benchmarks', '{}'), true);

        return view('admin.activation.benchmarks', [
            'aggregate' => $aggregate,
            'benchmarks' => $benchmarkData,
            'hasBenchmarks' => ! empty($benchmarkData['metrics'] ?? []),
        ]);
    }
}
