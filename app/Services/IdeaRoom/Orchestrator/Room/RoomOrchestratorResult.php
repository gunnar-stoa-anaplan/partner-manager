<?php

namespace App\Services\IdeaRoom\Orchestrator\Room;

use App\Models\IdeaRoom\RoomMessage;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class RoomOrchestratorResult
{
    /**
     * @param  ToolResult[]  $actions
     * @param  array{prompt_tokens?: int, completion_tokens?: int, total_tokens?: int}  $usage
     */
    public function __construct(
        public readonly string $message,
        public readonly array $actions,
        public readonly array $usage,
        public readonly RoomMessage $userMessage,
        public readonly RoomMessage $agentMessage,
    ) {}
}
