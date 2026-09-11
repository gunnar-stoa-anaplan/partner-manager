<?php

namespace App\Mcp\Tools;

use App\Models\Signal;
use App\Services\PartnerHealthService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SignalExplainTool extends Tool
{
    protected string $name = 'signal_explain';

    protected string $description = 'Deep-dive ONE signal by signal_id: the signal itself, correlated signals, the partner\'s health context, and the underlying metrics that generated it. Discover signal ids first with signal_feed.';

    public function handle(Request $request): Response
    {
        $signal = Signal::with('partner', 'linkedAlert')->findOrFail($request->get('signal_id'));

        $correlatedSignals = $signal->getCorrelatedSignals()->map(fn (Signal $s) => [
            'id' => $s->id,
            'channel' => $s->channel,
            'type' => $s->type,
            'severity' => $s->severity,
            'title' => $s->title,
            'confidence' => $s->confidence_score,
        ]);

        $partnerContext = null;
        if ($signal->partner) {
            $healthService = app(PartnerHealthService::class);
            $health = $healthService->computeForPartner($signal->partner);

            $activeSignalsByChannel = Signal::where('partner_id', $signal->partner_id)
                ->active()
                ->get()
                ->groupBy('channel')
                ->map->count();

            $partnerContext = [
                'name' => $signal->partner->name,
                'health_score' => $health['score'] ?? null,
                'risk_level' => $health['risk_level'] ?? null,
                'dimensions' => $health['dimensions'] ?? [],
                'active_signals_by_channel' => $activeSignalsByChannel->toArray(),
                'total_active_signals' => $activeSignalsByChannel->sum(),
            ];
        }

        return Response::json([
            'signal' => [
                'id' => $signal->id,
                'channel' => $signal->channel,
                'type' => $signal->type,
                'severity' => $signal->severity,
                'title' => $signal->title,
                'description' => $signal->description,
                'recommended_action' => $signal->recommended_action,
                'confidence' => $signal->confidence_score,
                'source_processor' => $signal->source_processor,
                'status' => $signal->status,
                'data' => $signal->data,
                'related_entity_ids' => $signal->related_entity_ids,
                'created_at' => $signal->created_at->toDateTimeString(),
                'expires_at' => $signal->expires_at?->toDateTimeString(),
            ],
            'correlated_signals' => $correlatedSignals->all(),
            'partner_context' => $partnerContext,
            'linked_alert' => $signal->linkedAlert ? [
                'id' => $signal->linkedAlert->id,
                'type' => $signal->linkedAlert->type,
                'severity' => $signal->linkedAlert->severity,
                'title' => $signal->linkedAlert->title,
                'status' => $signal->linkedAlert->status,
            ] : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'signal_id' => $schema->integer()->description('The signal ID to explain')->required(),
        ];
    }
}
