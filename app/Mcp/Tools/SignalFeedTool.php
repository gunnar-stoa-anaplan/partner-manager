<?php

namespace App\Mcp\Tools;

use App\Models\Signal;
use App\Support\DatabasePortability;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SignalFeedTool extends Tool
{
    protected string $name = 'signal_feed';

    protected string $description = 'The Signal Engine\'s prioritized feed of insights across partners in your scope. Filter by channel, severity, partner_id, type, or status. Returns each signal\'s description and recommended action — the "what needs attention" queue. For the full context behind ONE signal, use signal_explain.';

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
            'channel' => $schema->string()->enum(['risk', 'opportunity', 'trend', 'anomaly', 'recommendation', 'benchmark'])->description('Filter by signal channel'),
            'severity' => $schema->string()->enum(['critical', 'warning', 'info', 'positive'])->description('Filter by severity'),
            'partner_id' => $schema->integer()->description('Filter to one partner (numeric id from partner_list)'),
            'type' => $schema->string()->description('Filter by signal type (partial match)'),
            'status' => $schema->string()->enum(['active', 'acknowledged', 'resolved', 'expired', 'dismissed'])->description('Signal lifecycle status (default: active)'),
            'limit' => $schema->integer()->description('Max results (default 20, max 50)'),
        ];
    }
}
