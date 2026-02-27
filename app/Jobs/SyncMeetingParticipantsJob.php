<?php

namespace App\Jobs;

use App\Models\ZoomMeeting;
use App\Services\WorkshopMeetingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncMeetingParticipantsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 30;

    public function __construct(
        private int $meetingId,
    ) {}

    public function handle(WorkshopMeetingService $service): void
    {
        $meeting = ZoomMeeting::findOrFail($this->meetingId);

        $service->syncParticipants($meeting);
    }
}
