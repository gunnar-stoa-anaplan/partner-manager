<?php

namespace App\Console\Commands;

use App\Services\SignalEngineService;
use Illuminate\Console\Command;

class ProcessSignals extends Command
{
    protected $signature = 'signal:process {--cadence= : Run only processors matching this cadence}';

    protected $description = 'Run the Signal Engine to analyze partner data and generate signals';

    public function handle(SignalEngineService $service): int
    {
        $cadence = $this->option('cadence');

        $this->info('Running Signal Engine'.($cadence ? " (cadence: {$cadence})" : '').'...');

        $run = $service->runAll($cadence);

        if ($run->status === 'failed') {
            $this->error("Engine run failed: {$run->error}");

            return self::FAILURE;
        }

        $this->info("Completed in {$run->duration_ms}ms");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Partners processed', $run->partners_processed],
                ['Signals created', $run->signals_created],
                ['Signals expired', $run->signals_expired],
                ['Signals correlated', $run->signals_correlated],
            ]
        );

        return self::SUCCESS;
    }
}
