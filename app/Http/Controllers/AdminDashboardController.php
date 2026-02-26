<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ── Section 1: Import History ──

        $totalImports = Import::count();

        $recentImports = Import::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $importStats = Import::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $importTimeline = Import::select(
            DB::raw('YEARWEEK(created_at, 1) as week'),
            DB::raw('MIN(DATE(created_at)) as week_start'),
            DB::raw('SUM(successful_rows) as successful_rows'),
            DB::raw('SUM(failed_rows) as failed_rows')
        )
            ->where('created_at', '>=', now()->subWeeks(12))
            ->groupBy(DB::raw('YEARWEEK(created_at, 1)'))
            ->orderBy('week')
            ->get();

        // ── Section 2: User Access ──

        $usersByRole = User::select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role');

        $allUsers = User::withCount('partners')
            ->orderBy('name')
            ->get();

        $recentUsers = User::where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        // ── Section 3: Database & System Health ──

        $dbName = config('database.connections.mysql.database', 'laravel');

        $tableStats = collect(DB::select("
            SELECT TABLE_NAME as table_name,
                   TABLE_ROWS as row_count,
                   ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb,
                   ROUND(DATA_LENGTH / 1024 / 1024, 2) as data_mb,
                   ROUND(INDEX_LENGTH / 1024 / 1024, 2) as index_mb
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
              AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
        ", [$dbName]));

        $dbTotalSizeMb = round($tableStats->sum('size_mb'), 2);
        $dbTotalRows = $tableStats->sum('row_count');

        $mysqlStatus = collect(DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN (
            'Uptime', 'Threads_connected', 'Threads_running',
            'Questions', 'Slow_queries', 'Connections',
            'Innodb_buffer_pool_read_requests', 'Innodb_buffer_pool_reads'
        )"))->pluck('Value', 'Variable_name');

        $mysqlUptime = (int) ($mysqlStatus['Uptime'] ?? 0);
        $mysqlUptimeDays = round($mysqlUptime / 86400, 1);
        $threadsConnected = (int) ($mysqlStatus['Threads_connected'] ?? 0);
        $threadsRunning = (int) ($mysqlStatus['Threads_running'] ?? 0);
        $totalQueries = (int) ($mysqlStatus['Questions'] ?? 0);
        $slowQueries = (int) ($mysqlStatus['Slow_queries'] ?? 0);
        $totalConnections = (int) ($mysqlStatus['Connections'] ?? 0);

        $bufferReqs = (int) ($mysqlStatus['Innodb_buffer_pool_read_requests'] ?? 0);
        $bufferReads = (int) ($mysqlStatus['Innodb_buffer_pool_reads'] ?? 0);
        $bufferHitRate = $bufferReqs > 0 ? round((1 - $bufferReads / $bufferReqs) * 100, 2) : 0;

        $mysqlVariables = collect(DB::select("SHOW GLOBAL VARIABLES WHERE Variable_name IN (
            'innodb_buffer_pool_size', 'max_connections', 'long_query_time'
        )"))->pluck('Value', 'Variable_name');

        $bufferPoolSizeMb = round((int) ($mysqlVariables['innodb_buffer_pool_size'] ?? 0) / 1024 / 1024);
        $maxConnections = (int) ($mysqlVariables['max_connections'] ?? 0);
        $longQueryTime = (float) ($mysqlVariables['long_query_time'] ?? 10);

        $phpMemoryUsage = round(memory_get_usage(true) / 1024 / 1024, 1);
        $phpMemoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
        $phpMemoryLimit = ini_get('memory_limit');

        return view('admin.dashboard', compact(
            'totalImports',
            'recentImports', 'importStats', 'importTimeline',
            'usersByRole', 'allUsers', 'recentUsers',
            'tableStats', 'dbTotalSizeMb', 'dbTotalRows', 'dbName',
            'mysqlUptimeDays', 'threadsConnected', 'threadsRunning',
            'totalQueries', 'slowQueries', 'totalConnections',
            'bufferHitRate', 'bufferPoolSizeMb', 'maxConnections', 'longQueryTime',
            'phpMemoryUsage', 'phpMemoryPeak', 'phpMemoryLimit'
        ));
    }
}
