<?php

namespace App\Mcp\Tools;

use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get Integration Hub status: active connections, recent jobs, and failure rates.')]
class IntegrationStatusTool extends Tool
{
    public function handle(Request $request): Response
    {
        $connections = IntegrationConnection::query()
            ->withCount('jobs')
            ->withCount(['jobs as failed_jobs_count' => fn ($q) => $q->where('status', 'failed')])
            ->get();

        $recentJobs = IntegrationJob::query()
            ->with('connection:id,name')
            ->latest()
            ->limit(10)
            ->get();

        return Response::json([
            'connections' => $connections->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'status' => $c->status,
                'direction' => $c->direction,
                'jobs_count' => $c->jobs_count,
                'failed_jobs_count' => $c->failed_jobs_count,
                'last_tested_at' => $c->last_tested_at?->toIso8601String(),
                'last_sync_at' => $c->last_sync_at?->toIso8601String(),
            ])->all(),
            'recent_jobs' => $recentJobs->map(fn ($j) => [
                'id' => $j->id,
                'connection' => $j->connection->name ?? 'Deleted',
                'direction' => $j->direction,
                'status' => $j->status,
                'total_rows' => $j->total_rows,
                'rows_processed' => $j->rows_processed,
                'rows_failed' => $j->rows_failed,
                'started_at' => $j->started_at?->toIso8601String(),
            ])->all(),
            'summary' => [
                'total_connections' => $connections->count(),
                'active_connections' => $connections->where('status', 'active')->count(),
                'jobs_today' => IntegrationJob::whereDate('created_at', today())->count(),
                'failed_24h' => IntegrationJob::where('status', 'failed')
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
            ],
        ]);
    }
}
