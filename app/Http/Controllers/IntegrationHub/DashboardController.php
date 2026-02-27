<?php

namespace App\Http\Controllers\IntegrationHub;

use App\Http\Controllers\Controller;
use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $connections = IntegrationConnection::query()
            ->withCount('jobs')
            ->latest()
            ->get();

        $recentJobs = IntegrationJob::query()
            ->with('connection')
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'total_connections' => IntegrationConnection::count(),
            'active_connections' => IntegrationConnection::where('status', 'active')->count(),
            'jobs_today' => IntegrationJob::whereDate('created_at', today())->count(),
            'failed_jobs_24h' => IntegrationJob::where('status', 'failed')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'rows_imported_30d' => IntegrationJob::where('direction', 'import')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('rows_processed'),
            'rows_exported_30d' => IntegrationJob::where('direction', 'export')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('rows_processed'),
        ];

        return view('integration-hub.dashboard', compact('connections', 'recentJobs', 'stats'));
    }
}
