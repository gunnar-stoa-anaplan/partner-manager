<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Services\AnaplanApiService;
use Illuminate\Console\Command;

class AnaplanSyncCommand extends Command
{
    protected $signature = 'anaplan:sync
        {--with-notifications : Send version outdated alerts after sync}
        {--skip-modules : Skip module/list sync for faster execution}';

    protected $description = 'Sync workspaces and models from the Anaplan API with version detection';

    public function handle(AnaplanApiService $service): int
    {
        if (! $service->isConfigured()) {
            $this->error('Anaplan API credentials are not configured. Set ANAPLAN_USERNAME and ANAPLAN_PASSWORD in .env');

            return self::FAILURE;
        }

        $includeModules = ! $this->option('skip-modules');

        $import = Import::create([
            'type' => 'anaplan_sync',
            'source' => 'api',
            'status' => 'processing',
            'user_id' => 1,
            'started_at' => now(),
            'summary' => [
                'options' => [
                    'skip_modules' => ! $includeModules,
                    'with_notifications' => (bool) $this->option('with-notifications'),
                ],
            ],
        ]);

        $this->info('Starting Anaplan sync...');

        if (! $includeModules) {
            $this->info('Skipping module/list sync.');
        }

        $result = $service->syncAll($includeModules);

        $this->info("Workspaces synced: {$result['workspaces_synced']}");
        $this->info("Models synced: {$result['models_synced']}");

        if ($result['models_archived'] > 0) {
            $this->info("Models archived: {$result['models_archived']}");
        }

        if ($result['workspaces_linked'] > 0) {
            $this->info("Workspaces linked to partners: {$result['workspaces_linked']}");
        }

        if (! empty($result['errors'])) {
            $this->warn('Errors encountered:');
            foreach ($result['errors'] as $error) {
                $this->warn("  - {$error}");
            }
        }

        $alertsSent = 0;

        if ($this->option('with-notifications')) {
            $this->info('Checking for version alerts...');
            $alerts = $service->checkVersionAlerts();

            if (empty($alerts)) {
                $this->info('No version alerts to send.');
            } else {
                $alertsSent = $service->sendVersionAlerts($alerts);
                $this->info("Version alerts queued: {$alertsSent} (of ".count($alerts).' outdated partners)');
            }
        }

        $import->update([
            'status' => empty($result['errors']) ? 'completed' : 'failed',
            'completed_at' => now(),
            'total_rows' => $result['workspaces_synced'] + $result['models_synced'],
            'successful_rows' => $result['models_synced'],
            'failed_rows' => count($result['errors']),
            'error_log' => ! empty($result['errors']) ? implode("\n", $result['errors']) : null,
            'summary' => [
                'workspaces_synced' => $result['workspaces_synced'],
                'workspaces_linked' => $result['workspaces_linked'],
                'models_synced' => $result['models_synced'],
                'models_archived' => $result['models_archived'],
                'version_alerts_sent' => $alertsSent,
                'options' => [
                    'skip_modules' => ! $includeModules,
                    'with_notifications' => (bool) $this->option('with-notifications'),
                ],
            ],
        ]);

        $this->info('Anaplan sync complete.');

        return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
    }
}
