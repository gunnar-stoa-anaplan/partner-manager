<?php

namespace App\Console\Commands;

use App\Services\GoalComputationService;
use Illuminate\Console\Command;

class ComputeGoalActuals extends Command
{
    protected $signature = 'goals:compute {--fy= : Fiscal year to compute (e.g. FY27). Defaults to all.}';

    protected $description = 'Compute actual values for all assigned goals from live data sources';

    public function handle(GoalComputationService $service): int
    {
        $fy = $this->option('fy');

        $this->info('Computing goal actuals'.($fy ? " for {$fy}" : ' for all fiscal years').'...');

        $count = $service->computeAll($fy);

        $this->info("Updated {$count} goal(s).");

        return self::SUCCESS;
    }
}
