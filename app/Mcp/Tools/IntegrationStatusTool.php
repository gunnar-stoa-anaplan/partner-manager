<?php

namespace App\Mcp\Tools;

use App\Models\Contact;
use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use App\Models\User;
use App\Support\VisibilityContext;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class IntegrationStatusTool extends Tool
{
    protected string $name = 'integration_status';

    protected string $description = 'Integration Hub health: connections with per-connection job and failure counts, the 10 most recent jobs, and a today/24h summary. This is about data-sync plumbing, not partner business data. Admin / Integration Hub access only. Takes no arguments.';

    /**
     * Integration Hub state is org-level infrastructure with no partner axis to
     * scope by, so access is capability-gated (not row-scoped) — mirroring the
     * web Integration Hub gate (EnsureAdminOrIntegration): admin-bypass or the
     * integration_hub.hub permission. Hidden from callers who lack it; also
     * hard-guarded in handle() as defense in depth.
     */
    public function shouldRegister(VisibilityContext $visibility): bool
    {
        return $this->authorizedFor($visibility->actor());
    }

    /**
     * A null actor is the local stdio / console context (an admin-equivalent
     * environment whose operator already has full host access); a portal Contact
     * is never an integration admin.
     */
    private function authorizedFor(User|Contact|null $actor): bool
    {
        if ($actor === null) {
            return true;
        }

        return $actor instanceof User
            && ($actor->bypassesPermissions() || $actor->hasModulePermission('integration_hub.hub'));
    }

    public function handle(Request $request): Response
    {
        if (! $this->authorizedFor(app(VisibilityContext::class)->actor())) {
            return Response::error('Not authorized: integration status requires admin or Integration Hub access.');
        }

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
