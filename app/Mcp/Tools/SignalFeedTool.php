<?php

namespace App\Mcp\Tools;

use App\Models\Signal;
use App\Support\DatabasePortability;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Query active signals from the Signal Engine. Filter by channel (risk, opportunity, trend, anomaly, recommendation, benchmark), severity, partner, or signal type. Returns prioritized signal feed with descriptions and recommended actions.')]
class SignalFeedTool extends Tool
{
    public function handle(Request $request): Response
    {
        $query = Signal::with('partner');

        if ($channel = $request->get('channel')) {
            $query->where('channel', $channel);
        }

        if ($severity = $request->get('severity')) {
            $query->where('severity', $severity);
        }

        if ($partnerId = $request->get('partner_id')) {
            $query->where('partner_id', $partnerId);
        }

        if ($type = $request->get('type')) {
            $query->where('type', 'like', "%{$type}%");
        }

        $status = $request->get('status') ?? 'active';
        $query->where('status', $status);

        $limit = min($request->get('limit') ?? 20, 50);

        $signals = $query->orderByRaw(DatabasePortability::orderByValues('severity', DatabasePortability::SEVERITY_ORDER))
            ->orderByDesc('confidence_score')
            ->limit($limit)
            ->get();

        $summary = [
            'total' => $signals->count(),
            'by_channel' => $signals->groupBy('channel')->map->count()->toArray(),
            'by_severity' => $signals->groupBy('severity')->map->count()->toArray(),
            'critical_count' => $signals->where('severity', 'critical')->count(),
        ];

        return Response::json([
            'summary' => $summary,
            'signals' => $signals->map(fn (Signal $s) => [
                'id' => $s->id,
                'channel' => $s->channel,
                'type' => $s->type,
                'severity' => $s->severity,
                'title' => $s->title,
                'description' => $s->description,
                'recommended_action' => $s->recommended_action,
                'confidence' => $s->confidence_score,
                'partner' => $s->partner?->name,
                'partner_id' => $s->partner_id,
                'created_at' => $s->created_at->toDateTimeString(),
                'correlation_id' => $s->correlation_id,
            ])->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'channel' => $schema->string()->description('Filter by channel: risk, opportunity, trend, anomaly, recommendation, benchmark'),
            'severity' => $schema->string()->description('Filter by severity: critical, warning, info, positive'),
            'partner_id' => $schema->integer()->description('Filter by partner ID'),
            'type' => $schema->string()->description('Filter by signal type (partial match)'),
            'status' => $schema->string()->description('Signal status (default: active). Options: active, acknowledged, resolved, expired, dismissed'),
            'limit' => $schema->integer()->description('Max results (default 20, max 50)'),
        ];
    }
}
