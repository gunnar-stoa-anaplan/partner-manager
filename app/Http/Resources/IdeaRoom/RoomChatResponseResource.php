<?php

namespace App\Http\Resources\IdeaRoom;

use App\Services\IdeaRoom\Orchestrator\Room\RoomOrchestratorResult;
use App\Services\IdeaRoom\Orchestrator\ToolResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RoomOrchestratorResult */
class RoomChatResponseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->resource->message,
            'actions' => array_map(fn (ToolResult $action) => [
                'tool' => $action->tool,
                'success' => $action->success,
                'data' => $action->data,
                'error' => $action->error,
            ], $this->resource->actions),
            'usage' => $this->resource->usage,
            'user_message_uuid' => $this->resource->userMessage->uuid,
            'agent_message_uuid' => $this->resource->agentMessage->uuid,
        ];
    }
}
