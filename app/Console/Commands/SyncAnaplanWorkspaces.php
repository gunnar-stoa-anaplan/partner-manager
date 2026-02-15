<?php

namespace App\Console\Commands;

use App\Services\AnaplanApiService;
use Illuminate\Console\Command;

class SyncAnaplanWorkspaces extends Command
{
    protected $signature = 'anaplan:sync';

    protected $description = 'Sync workspaces, models, modules, and lists from the Anaplan API';

    public function handle(AnaplanApiService $service): int
    {
        if (! $service->isConfigured()) {
            $this->error('Anaplan API credentials are not configured. Set ANAPLAN_USERNAME and ANAPLAN_PASSWORD in .env.');

            return self::FAILURE;
        }

        $this->info('Starting Anaplan workspace sync...');
        $result = $service->syncAll();

        $this->info("Synced {$result['workspaces_synced']} workspaces and {$result['models_synced']} models.");

        foreach ($result['errors'] as $error) {
            $this->warn($error);
        }

        return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
    }
}
