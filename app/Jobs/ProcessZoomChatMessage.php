<?php

namespace App\Jobs;

use App\Models\ZoomChatMessage;
use App\Services\ZoomChatDispatcherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessZoomChatMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $backoff = 10;

    public function __construct(public ZoomChatMessage $chatMessage) {}

    public function handle(ZoomChatDispatcherService $dispatcher): void
    {
        $dispatcher->processMessage($this->chatMessage);
    }
}
