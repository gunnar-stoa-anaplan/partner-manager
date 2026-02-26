<?php

namespace App\Jobs;

use App\Models\EnablementSession;
use App\Models\User;
use App\Services\WorkshopMeetingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateZoomMeetingJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public int $backoff = 10;

    public function __construct(
        private int $sessionId,
        private int $createdBy,
    ) {}

    public function handle(WorkshopMeetingService $service): void
    {
        $session = EnablementSession::findOrFail($this->sessionId);
        $creator = User::findOrFail($this->createdBy);

        $service->createForSession($session, $creator);
    }
}
