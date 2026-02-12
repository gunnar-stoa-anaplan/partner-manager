<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\TelemetryEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelemetryService
{
    public function collectHeartbeat(): array
    {
        $metrics = [
            'instance_id' => $this->getInstanceId(),
            'app_version' => config('app.version', '1.0.0'),
            'license_key' => AppSetting::get('license_key'),
            'collected_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            
            // Usage metrics
            'user_counts' => [
                'total' => DB::table('users')->count(),
                'admin' => DB::table('users')->where('role', 'admin')->count(),
                'psm' => DB::table('users')->where('role', 'psm')->count(),
                'manager' => DB::table('users')->where('role', 'manager')->count(),
                'active_30d' => DB::table('users')->where('updated_at', '>=', now()->subDays(30))->count(),
            ],
            'partner_counts' => [
                'total' => DB::table('partners')->count(),
                'with_pipeline' => DB::table('opportunities')
                    ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
                    ->distinct('partner_id')->count('partner_id'),
                'with_projects' => DB::table('projects')
                    ->where('status', 'in_progress')
                    ->distinct('partner_id')->count('partner_id'),
            ],
            'data_volumes' => [
                'opportunities' => DB::table('opportunities')->count(),
                'projects' => DB::table('projects')->count(),
                'contacts' => DB::table('contacts')->count(),
                'imports_30d' => DB::table('imports')->where('created_at', '>=', now()->subDays(30))->count(),
                'certifications' => DB::table('certifications')->count(),
                'blueprint_assignments' => DB::table('partner_blueprint_assignments')->count(),
                'partner_tasks' => DB::table('partner_tasks')->count(),
                'alerts_active' => DB::table('partner_alerts')->where('status', 'active')->count(),
            ],
            'feature_usage' => [
                'paths_active' => DB::table('partner_path_enrollments')->where('status', 'active')->count(),
                'scorecards_computed' => DB::table('partner_scorecards')->where('scored_at', '>=', now()->subDays(7))->count(),
                'qbrs_completed_30d' => DB::table('qbr_sessions')->where('status', 'completed')
                    ->where('completed_date', '>=', now()->subDays(30))->count(),
                'notifications_sent_30d' => DB::table('notification_logs')->where('status', 'sent')
                    ->where('sent_at', '>=', now()->subDays(30))->count(),
            ],
            'system_health' => [
                'db_connection' => $this->checkDbHealth(),
                'storage_free_pct' => $this->getStorageFreePercent(),
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'last_import_at' => DB::table('imports')->orderByDesc('created_at')->value('created_at'),
                'uptime_seconds' => defined('LARAVEL_START') ? microtime(true) - LARAVEL_START : null,
            ],
            'critical_events' => $this->getRecentCriticalEvents(),
        ];
        
        return $metrics;
    }
    
    public function sendHeartbeat(): bool
    {
        $endpoint = AppSetting::get('telemetry_endpoint');
        if (!$endpoint) {
            Log::info('Telemetry: No endpoint configured, skipping send.');
            return false;
        }
        
        $metrics = $this->collectHeartbeat();
        $apiKey = AppSetting::get('telemetry_api_key');
        
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Api-Key' => $apiKey ?? '',
                    'X-Instance-Id' => $metrics['instance_id'],
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $metrics);
            
            // Log the event
            DB::table('telemetry_heartbeats')->insert([
                'metrics' => json_encode($metrics),
                'sent_at' => $response->successful() ? now() : null,
                'status' => $response->successful() ? 'sent' : 'failed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            if (!$response->successful()) {
                Log::warning('Telemetry: Failed to send heartbeat', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
            
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telemetry: Exception sending heartbeat', ['error' => $e->getMessage()]);
            
            DB::table('telemetry_heartbeats')->insert([
                'metrics' => json_encode($metrics),
                'sent_at' => null,
                'status' => 'failed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return false;
        }
    }
    
    public function recordEvent(string $type, array $payload = []): void
    {
        TelemetryEvent::create([
            'event_type' => $type,
            'payload' => array_merge($payload, [
                'instance_id' => $this->getInstanceId(),
                'timestamp' => now()->toIso8601String(),
            ]),
        ]);
    }
    
    public function sendPendingEvents(): int
    {
        $endpoint = AppSetting::get('telemetry_endpoint');
        if (!$endpoint) return 0;
        
        $events = TelemetryEvent::where('status', 'pending')->limit(100)->get();
        if ($events->isEmpty()) return 0;
        
        $apiKey = AppSetting::get('telemetry_api_key');
        $eventsEndpoint = rtrim($endpoint, '/') . '/events';
        
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Api-Key' => $apiKey ?? '',
                    'X-Instance-Id' => $this->getInstanceId(),
                ])
                ->post($eventsEndpoint, ['events' => $events->toArray()]);
            
            if ($response->successful()) {
                TelemetryEvent::whereIn('id', $events->pluck('id'))
                    ->update(['status' => 'sent', 'sent_at' => now()]);
                return $events->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Telemetry: Failed to send events', ['error' => $e->getMessage()]);
        }
        
        return 0;
    }
    
    protected function getInstanceId(): string
    {
        $id = AppSetting::get('instance_id');
        if (!$id) {
            $id = 'pm-' . substr(md5(config('app.key', '') . config('app.url', '')), 0, 12);
            AppSetting::set('instance_id', $id);
        }
        return $id;
    }
    
    protected function checkDbHealth(): string
    {
        try {
            DB::select('SELECT 1');
            return 'healthy';
        } catch (\Throwable $e) {
            return 'unhealthy';
        }
    }
    
    protected function getStorageFreePercent(): ?float
    {
        $total = @disk_total_space('/');
        $free = @disk_free_space('/');
        if ($total && $free) {
            return round(($free / $total) * 100, 1);
        }
        return null;
    }
    
    protected function getRecentCriticalEvents(): array
    {
        return DB::table('partner_alerts')
            ->where('severity', 'critical')
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subDays(7))
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
