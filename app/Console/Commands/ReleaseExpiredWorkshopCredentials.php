<?php

namespace App\Console\Commands;

use App\Models\WorkshopCredentialAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ReleaseExpiredWorkshopCredentials extends Command
{
    protected $signature = 'workshops:release-expired-credentials';

    protected $description = 'Release Anaplan sandbox credential assignments whose hold has expired, back to the pool';

    public function handle(): int
    {
        $expired = WorkshopCredentialAssignment::whereNull('released_at')
            ->whereNotNull('hold_until')
            ->where('hold_until', '<', Carbon::today())
            ->get();

        foreach ($expired as $assignment) {
            $assignment->update([
                'released_at' => Carbon::now(),
                'released_reason' => 'session_ended',
            ]);
        }

        $this->info("Released {$expired->count()} expired credential assignment(s).");

        return self::SUCCESS;
    }
}
