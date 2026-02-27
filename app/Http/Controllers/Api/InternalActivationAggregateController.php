<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\ActivationAggregateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalActivationAggregateController extends Controller
{
    public function __invoke(ActivationAggregateService $service): JsonResponse
    {
        return response()->json($service->buildAggregate());
    }

    public function storeBenchmarks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cohort_size' => 'required|integer|min:1',
            'metrics' => 'required|array',
            'computed_date' => 'required|date',
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'activation_benchmarks'],
            ['value' => json_encode($validated)]
        );

        return response()->json(['status' => 'ok'], 201);
    }
}
