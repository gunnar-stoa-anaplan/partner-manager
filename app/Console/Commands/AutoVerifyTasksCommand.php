<?php

namespace App\Console\Commands;

use App\Services\TaskAutoVerificationService;
use Illuminate\Console\Command;

class AutoVerifyTasksCommand extends Command
{
    protected $signature = 'tasks:auto-verify';
    protected $description = 'Auto-verify partner tasks that have system-verifiable completion rules';

    public function handle(TaskAutoVerificationService $service): int
    {
        $this->info('Auto-verifying partner tasks...');
        $count = $service->verifyAll();
        $this->info("Auto-verified {$count} tasks.");
        return self::SUCCESS;
    }
}
