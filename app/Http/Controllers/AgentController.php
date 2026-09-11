<?php

namespace App\Http\Controllers;

use App\Services\Agent\ScreenAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints for the embedded screen agent. Runs as the authenticated user, so
 * the ScreenAgentService builds context from row-level-scoped data only — the
 * agent cannot surface anything the user cannot already see. Inert unless the
 * agent feature is enabled.
 */
class AgentController extends Controller
{
    public function __construct(private readonly ScreenAgentService $agent)
    {
        abort_unless((bool) config('agent.enabled'), 404);
    }

    public function insights(Request $request): JsonResponse
    {
        $data = $request->validate([
            'screen' => ['required', 'string', 'max:64'],
            'filters' => ['sometimes', 'array'],
        ]);

        $context = $this->agent->contextFor($data['screen'], $data['filters'] ?? []);
        $provider = $this->agent->provider();

        return response()->json([
            'provider' => $provider->name(),
            'scope' => $context->scopeLabel(),
            'insights' => $provider->insights($context),
        ]);
    }

    public function ask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'screen' => ['required', 'string', 'max:64'],
            'question' => ['required', 'string', 'max:1000'],
            'filters' => ['sometimes', 'array'],
        ]);

        $context = $this->agent->contextFor($data['screen'], $data['filters'] ?? []);
        $provider = $this->agent->provider();

        return response()->json([
            'provider' => $provider->name(),
            'scope' => $context->scopeLabel(),
            'answer' => $provider->answer($context, $data['question']),
        ]);
    }
}
