<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\SignalAggregateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalSignalAggregateController extends Controller
{
    public function __invoke(SignalAggregateService $service): JsonResponse
    {
        $aggregate = $service->buildAggregate();

        if ($aggregate === null) {
            return response()->json(['status' => 'disabled', 'message' => 'Signal sharing is not enabled.'], 204);
        }

        return response()->json($aggregate);
    }

    public function storeBenchmarks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cohort_size' => 'required|integer|min:1',
            'metrics' => 'required|array',
            'computed_date' => 'required|date',
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'signal_engine_benchmarks'],
            ['value' => json_encode($validated)]
        );

        return response()->json(['status' => 'ok'], 201);
    }
}
