<?php

namespace App\Jobs;

use App\Models\EnablementSession;
use App\Services\WorkshopMeetingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CancelZoomMeetingJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public int $backoff = 10;

    public function __construct(
        private int $sessionId,
    ) {}

    public function handle(WorkshopMeetingService $service): void
    {
        $session = EnablementSession::with('zoomMeeting')->findOrFail($this->sessionId);

        $service->cancelForSession($session);
    }
}
