<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use App\Services\ActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InternalMetricsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'partner_count' => Partner::count(),
            'user_count' => User::count(),
            'app_version' => config('app.version', 'unknown'),
            'error_count_24h' => $this->errorCount24h(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'pending_jobs' => DB::table('jobs')->count(),
            'db_connected' => $this->isDatabaseConnected(),
            'db_connection_count' => $this->activeConnectionCount(),
            'storage_used_mb' => $this->storageUsedMB(),
            'cache_hit_ratio' => $this->cacheHitRatio(),
            'pending_migrations' => $this->pendingMigrationCount(),
            'activation' => app(ActivationService::class)->getSnapshot(),
        ]);
    }

    private function errorCount24h(): int
    {
        $logFile = storage_path('logs/laravel.log');

        if (! file_exists($logFile)) {
            return 0;
        }

        $cutoff = now()->subDay()->format('Y-m-d');
        $count = 0;

        $handle = fopen($logFile, 'r');
        if ($handle === false) {
            return 0;
        }

        // Read from the end of the file for efficiency
        fseek($handle, max(0, filesize($logFile) - 512000)); // Last ~500KB
        while (($line = fgets($handle)) !== false) {
            if (str_contains($line, '.ERROR:') && str_starts_with($line, '['.$cutoff)) {
                $count++;
            } elseif (str_contains($line, '.ERROR:') && str_starts_with($line, '['.now()->format('Y-m-d'))) {
                $count++;
            }
        }
        fclose($handle);

        return $count;
    }

    private function isDatabaseConnected(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function activeConnectionCount(): int
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                return (int) (DB::selectOne('SELECT count(*) AS c FROM pg_stat_activity')->c ?? 0);
            }
            if ($driver === 'mysql' || $driver === 'mariadb') {
                $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");

                return (int) ($result[0]->Value ?? 0);
            }

            return 0;
        } catch (\Throwable) {
            return -1;
        }
    }

    private function storageUsedMB(): float
    {
        $storagePath = storage_path();

        if (! is_dir($storagePath)) {
            return 0;
        }

        try {
            $output = shell_exec("du -sm {$storagePath} 2>/dev/null");

            return (float) ($output ? explode("\t", $output)[0] : 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    private function cacheHitRatio(): float
    {
        if (config('cache.default') !== 'redis') {
            return -1;
        }

        try {
            $info = app('redis')->info('stats');
            $hits = $info['keyspace_hits'] ?? 0;
            $misses = $info['keyspace_misses'] ?? 0;
            $total = $hits + $misses;

            return $total > 0 ? round($hits / $total, 4) : 0;
        } catch (\Throwable) {
            return -1;
        }
    }

    private function pendingMigrationCount(): int
    {
        try {
            $ran = DB::table('migrations')->pluck('migration')->toArray();
            $files = glob(database_path('migrations/*.php'));
            $pending = 0;

            foreach ($files as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                if (! in_array($name, $ran)) {
                    $pending++;
                }
            }

            return $pending;
        } catch (\Throwable) {
            return -1;
        }
    }
}
