<?php

namespace App\Console\Commands;

use App\Services\AlertGenerationService;
use Illuminate\Console\Command;

class GenerateAlertsCommand extends Command
{
    protected $signature = 'partners:generate-alerts';
    protected $description = 'Generate partner alerts based on data analysis rules';

    public function handle(AlertGenerationService $service): int
    {
        $this->info('Generating partner alerts...');
        $count = $service->generateForAll();
        $this->info("Generated {$count} new alerts.");
        return self::SUCCESS;
    }
}
