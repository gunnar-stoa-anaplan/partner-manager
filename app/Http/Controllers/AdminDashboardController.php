<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\User;
use App\Support\DatabasePortability;
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
            DB::raw(DatabasePortability::yearWeek('created_at').' as week'),
            DB::raw('MIN('.DatabasePortability::extractDate('created_at').') as week_start'),
            DB::raw('SUM(successful_rows) as successful_rows'),
            DB::raw('SUM(failed_rows) as failed_rows')
        )
            ->where('created_at', '>=', now()->subWeeks(12))
            ->groupBy(DB::raw(DatabasePortability::yearWeek('created_at')))
            ->orderBy('week')
            ->get();

        // ── Section 2: User Access ──

        $usersByRole = User::query()
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.display_name as role', DB::raw('COUNT(*) as count'))
            ->groupBy('roles.display_name')
            ->pluck('count', 'role');

        $allUsers = User::withCount('partners')
            ->orderBy('name')
            ->get();

        $recentUsers = User::where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        // ── Section 3: Database & System Health ──

        // Driver-aware DB/server health. Wrapped so stats never 500 the page.
        $driver = DB::connection()->getDriverName();
        $dbName = DB::connection()->getDatabaseName();

        $tableStats = collect();
        $mysqlUptimeDays = 0.0;
        $threadsConnected = 0;
        $threadsRunning = 0;
        $totalQueries = 0;
        $slowQueries = 0;
        $totalConnections = 0;
        $bufferHitRate = 0;
        $bufferPoolSizeMb = 0;
        $maxConnections = 0;
        $longQueryTime = 0.0;

        try {
            if ($driver === 'mysql' || $driver === 'mariadb') {
                $tableStats = collect(DB::select("
                    SELECT TABLE_NAME as table_name, TABLE_ROWS as row_count,
                           ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb,
                           ROUND(DATA_LENGTH / 1024 / 1024, 2) as data_mb,
                           ROUND(INDEX_LENGTH / 1024 / 1024, 2) as index_mb
                    FROM information_schema.TABLES
                    WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
                    ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
                ", [$dbName]));

                $s = collect(DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN (
                    'Uptime','Threads_connected','Threads_running','Questions','Slow_queries',
                    'Connections','Innodb_buffer_pool_read_requests','Innodb_buffer_pool_reads'
                )"))->pluck('Value', 'Variable_name');
                $mysqlUptimeDays = round((int) ($s['Uptime'] ?? 0) / 86400, 1);
                $threadsConnected = (int) ($s['Threads_connected'] ?? 0);
                $threadsRunning = (int) ($s['Threads_running'] ?? 0);
                $totalQueries = (int) ($s['Questions'] ?? 0);
                $slowQueries = (int) ($s['Slow_queries'] ?? 0);
                $totalConnections = (int) ($s['Connections'] ?? 0);
                $reqs = (int) ($s['Innodb_buffer_pool_read_requests'] ?? 0);
                $reads = (int) ($s['Innodb_buffer_pool_reads'] ?? 0);
                $bufferHitRate = $reqs > 0 ? round((1 - $reads / $reqs) * 100, 2) : 0;

                $v = collect(DB::select("SHOW GLOBAL VARIABLES WHERE Variable_name IN (
                    'innodb_buffer_pool_size','max_connections','long_query_time'
                )"))->pluck('Value', 'Variable_name');
                $bufferPoolSizeMb = round((int) ($v['innodb_buffer_pool_size'] ?? 0) / 1024 / 1024);
                $maxConnections = (int) ($v['max_connections'] ?? 0);
                $longQueryTime = (float) ($v['long_query_time'] ?? 10);
            } elseif ($driver === 'pgsql') {
                $tableStats = collect(DB::select('
                    SELECT relname AS table_name, n_live_tup AS row_count,
                           ROUND(pg_total_relation_size(relid)/1024.0/1024.0, 2) AS size_mb,
                           ROUND(pg_relation_size(relid)/1024.0/1024.0, 2) AS data_mb,
                           ROUND(pg_indexes_size(relid)/1024.0/1024.0, 2) AS index_mb
                    FROM pg_stat_user_tables
                    ORDER BY pg_total_relation_size(relid) DESC
                '));

                $m = DB::selectOne("
                    SELECT extract(epoch FROM (now() - pg_postmaster_start_time())) AS uptime,
                           (SELECT count(*) FROM pg_stat_activity) AS conns,
                           (SELECT count(*) FROM pg_stat_activity WHERE state = 'active') AS active,
                           (SELECT COALESCE(sum(xact_commit + xact_rollback),0) FROM pg_stat_database) AS xacts,
                           (SELECT CASE WHEN sum(blks_hit + blks_read) > 0
                                        THEN round(sum(blks_hit) * 100.0 / sum(blks_hit + blks_read), 2)
                                        ELSE 0 END FROM pg_stat_database) AS hit,
                           (SELECT setting::int FROM pg_settings WHERE name = 'max_connections') AS maxconn,
                           (SELECT pg_size_bytes(current_setting('shared_buffers')) / 1024 / 1024) AS bufmb
                ");
                $mysqlUptimeDays = round((float) ($m->uptime ?? 0) / 86400, 1);
                $threadsConnected = (int) ($m->conns ?? 0);
                $threadsRunning = (int) ($m->active ?? 0);
                $totalQueries = (int) ($m->xacts ?? 0);
                $totalConnections = (int) ($m->conns ?? 0);
                $bufferHitRate = (float) ($m->hit ?? 0);
                $maxConnections = (int) ($m->maxconn ?? 0);
                $bufferPoolSizeMb = (int) ($m->bufmb ?? 0);
            }
        } catch (\Throwable) {
            // leave safe defaults; the dashboard still renders
        }

        $dbTotalSizeMb = round($tableStats->sum('size_mb'), 2);
        $dbTotalRows = $tableStats->sum('row_count');

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
