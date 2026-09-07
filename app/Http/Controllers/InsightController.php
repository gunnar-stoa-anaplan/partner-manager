<?php

namespace App\Http\Controllers;

use App\Insights\MetricFilter;
use App\Insights\MetricRegistry;
use App\Support\VisibilityContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Uniform metric endpoint for the Insight Kit. Every metric — from any domain —
 * exposes value / drill / trend / breakdown here, and EVERY response is scoped
 * to the actor's row-level visibility (VisibilityContext → allowedPartners), so
 * an audit drill can never return rows the user may not see.
 */
class InsightController extends Controller
{
    public function __construct(
        private readonly MetricRegistry $registry,
        private readonly VisibilityContext $visibility,
    ) {}

    private function filter(Request $request): MetricFilter
    {
        return new MetricFilter(
            geo: $request->query('geo'),
            partner: $request->query('partner'),
            allowedPartners: $this->visibility->visiblePartnerNames(),
        );
    }

    public function meta(): JsonResponse
    {
        return response()->json(
            array_map(fn ($s) => $s->meta()->toArray(), array_values($this->registry->all()))
        );
    }

    public function value(Request $request, string $metric): JsonResponse
    {
        return response()->json($this->registry->get($metric)->value($this->filter($request))->toArray());
    }

    public function drill(Request $request, string $metric): JsonResponse
    {
        return response()->json($this->registry->get($metric)->drill($this->filter($request))->toArray());
    }

    public function trend(Request $request, string $metric): JsonResponse
    {
        return response()->json($this->registry->get($metric)->trend($this->filter($request)));
    }

    public function breakdown(Request $request, string $metric): JsonResponse
    {
        return response()->json(
            $this->registry->get($metric)->breakdown($request->query('dimension', 'stage'), $this->filter($request))
        );
    }
}
