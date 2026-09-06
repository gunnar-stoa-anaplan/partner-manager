<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemStatusService
{
    /**
     * Check database connectivity and return status details.
     *
     * @return array{status: string, driver: string, host: string, database: string, response_ms: int}
     */
    public function checkDatabase(): array
    {
        $driver = config('database.default');
        $host = config("database.connections.{$driver}.host", 'unknown');
        $database = config("database.connections.{$driver}.database", 'unknown');

        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseMs = (int) round((microtime(true) - $start) * 1000);

            return [
                'status' => 'healthy',
                'driver' => $driver,
                'host' => $host,
                'database' => $database,
                'response_ms' => $responseMs,
            ];
        } catch (\Throwable $e) {
            Log::warning('Database health check failed.', ['error' => $e->getMessage()]);

            return [
                'status' => 'unreachable',
                'driver' => $driver,
                'host' => $host,
                'database' => $database,
                'response_ms' => 0,
            ];
        }
    }

    /**
     * Check cache connectivity and return status details.
     *
     * @return array{status: string, driver: string}
     */
    public function checkCache(): array
    {
        $driver = config('cache.default');

        try {
            Cache::put('_health_check', true, 10);
            $result = Cache::get('_health_check');
            Cache::forget('_health_check');

            return [
                'status' => $result === true ? 'healthy' : 'degraded',
                'driver' => $driver,
            ];
        } catch (\Throwable $e) {
            Log::warning('Cache health check failed.', ['error' => $e->getMessage()]);

            return [
                'status' => 'unreachable',
                'driver' => $driver,
            ];
        }
    }

    /**
     * Return queue driver configuration.
     *
     * @return array{status: string, driver: string}
     */
    public function checkQueue(): array
    {
        $driver = config('queue.default');

        return [
            'status' => 'configured',
            'driver' => $driver,
        ];
    }

    /**
     * Return mail configuration (no credentials).
     *
     * @return array{status: string, mailer: string, host: string}
     */
    public function checkMail(): array
    {
        $mailer = config('mail.default');
        $host = config("mail.mailers.{$mailer}.host", 'not configured');

        return [
            'status' => $mailer !== 'log' ? 'configured' : 'log only',
            'mailer' => $mailer,
            'host' => $host,
        ];
    }

    /**
     * Read agent status from the shared volume.
     *
     * @return array{agent_version: string, instance_id: string, last_heartbeat: string, uptime_seconds: int, containers: array<int, array{name: string, image: string, state: string, status: string}>, is_connected: bool}|null
     */
    public function getAgentStatus(): ?array
    {
        $statusFile = rtrim(config('version.agent_status_path'), '/').'/agent-status.json';

        if (! file_exists($statusFile)) {
            return null;
        }

        $data = json_decode(file_get_contents($statusFile), true);

        if (! is_array($data) || ! isset($data['last_heartbeat'])) {
            return null;
        }

        $lastHeartbeat = Carbon::parse($data['last_heartbeat']);
        $data['is_connected'] = $lastHeartbeat->diffInMinutes(now()) < 2;

        return $data;
    }

    /**
     * Get runtime environment information.
     *
     * @return array{php_version: string, laravel_version: string, environment: string, debug: bool, memory_usage_mb: float, memory_peak_mb: float, memory_limit: string}
     */
    public function getRuntimeInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 1),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
            'memory_limit' => ini_get('memory_limit'),
        ];
    }

    /**
     * Get database server metrics.
     *
     * @return array{uptime_days: float, threads_connected: int, total_queries: int, slow_queries: int, max_connections: int}|null
     */
    public function getDatabaseMetrics(): ?array
    {
        $driver = DB::connection()->getDriverName();

        try {
            if ($driver === 'pgsql') {
                return $this->postgresMetrics();
            }
            if ($driver === 'mysql' || $driver === 'mariadb') {
                return $this->mysqlMetrics();
            }

            return null; // sqlite / other — no server metrics
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{uptime_days: float, threads_connected: int, total_queries: int, slow_queries: int, max_connections: int}
     */
    private function mysqlMetrics(): array
    {
        $status = collect(DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN (
            'Uptime', 'Threads_connected', 'Questions', 'Slow_queries'
        )"))->pluck('Value', 'Variable_name');

        $variables = collect(DB::select("SHOW GLOBAL VARIABLES WHERE Variable_name IN (
            'max_connections'
        )"))->pluck('Value', 'Variable_name');

        return [
            'uptime_days' => round((int) ($status['Uptime'] ?? 0) / 86400, 1),
            'threads_connected' => (int) ($status['Threads_connected'] ?? 0),
            'total_queries' => (int) ($status['Questions'] ?? 0),
            'slow_queries' => (int) ($status['Slow_queries'] ?? 0),
            'max_connections' => (int) ($variables['max_connections'] ?? 0),
        ];
    }

    /**
     * Postgres equivalents of the MySQL server metrics (slow-query count needs
     * pg_stat_statements, so it is reported as 0).
     *
     * @return array{uptime_days: float, threads_connected: int, total_queries: int, slow_queries: int, max_connections: int}
     */
    private function postgresMetrics(): array
    {
        $uptime = (float) (DB::selectOne('SELECT extract(epoch FROM (now() - pg_postmaster_start_time())) AS s')->s ?? 0);
        $conns = (int) (DB::selectOne('SELECT count(*) AS c FROM pg_stat_activity')->c ?? 0);
        $maxConn = (int) (DB::selectOne("SELECT setting AS v FROM pg_settings WHERE name='max_connections'")->v ?? 0);
        $xacts = (int) (DB::selectOne('SELECT COALESCE(sum(xact_commit + xact_rollback),0) AS q FROM pg_stat_database')->q ?? 0);

        return [
            'uptime_days' => round($uptime / 86400, 1),
            'threads_connected' => $conns,
            'total_queries' => $xacts,
            'slow_queries' => 0,
            'max_connections' => $maxConn,
        ];
    }
}
