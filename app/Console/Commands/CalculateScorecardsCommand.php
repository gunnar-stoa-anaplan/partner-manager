<?php

namespace App\Console\Commands;

use App\Services\ScorecardCalculationService;
use Illuminate\Console\Command;

class CalculateScorecardsCommand extends Command
{
    protected $signature = 'partners:calculate-scorecards';
    protected $description = 'Calculate partner scorecards across all 6 dimensions for all partners';

    public function handle(ScorecardCalculationService $service): int
    {
        $this->info('Calculating partner scorecards...');
        $count = $service->calculateForAllPartners();
        $this->info("Scorecards calculated for {$count} partners.");
        return self::SUCCESS;
    }
}
