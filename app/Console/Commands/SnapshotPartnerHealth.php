<?php

namespace App\Console\Commands;

use App\Services\PartnerHealthService;
use Illuminate\Console\Command;

class SnapshotPartnerHealth extends Command
{
    protected $signature = 'health:snapshot';

    protected $description = 'Compute and store daily health snapshots for all partners';

    public function handle(PartnerHealthService $service): int
    {
        $this->info('Computing health snapshots...');

        $count = $service->snapshotAll();

        $this->info("Created snapshots for {$count} partner(s).");

        return self::SUCCESS;
    }
}
