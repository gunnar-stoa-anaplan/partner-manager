<?php

namespace App\Console\Commands;

use App\Services\TelemetryService;
use Illuminate\Console\Command;

class TelemetryReportCommand extends Command
{
    protected $signature = 'telemetry:report {--dry-run : Show metrics without sending}';
    protected $description = 'Send telemetry heartbeat with usage metrics, system health, and critical events';

    public function handle(TelemetryService $service): int
    {
        if ($this->option('dry-run')) {
            $metrics = $service->collectHeartbeat();
            $this->info('Telemetry Heartbeat (dry run):');
            $this->line(json_encode($metrics, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $this->info('Sending telemetry heartbeat...');
        $sent = $service->sendHeartbeat();
        
        if ($sent) {
            $this->info('Heartbeat sent successfully.');
        } else {
            $this->warn('Heartbeat not sent (no endpoint configured or send failed).');
        }

        // Also send any pending events
        $eventCount = $service->sendPendingEvents();
        if ($eventCount > 0) {
            $this->info("Sent {$eventCount} pending telemetry events.");
        }

        return self::SUCCESS;
    }
}
