<?php

namespace App\Services\IdeaRoom\Orchestrator\Room\Tools;

use App\Enums\IdeaRoom\EventSource;
use App\Enums\IdeaRoom\EventType;
use App\Enums\IdeaRoom\LifecycleState;
use App\Models\IdeaRoom\IdeaEvent;
use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Room\Contracts\ExecutesRoomTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class RoomUpdateIdeaStateTool implements ExecutesRoomTool
{
    public function execute(Room $room, User $user, array $arguments): ToolResult
    {
        $idea = $room->idea;

        $newState = LifecycleState::tryFrom($arguments['new_state']);

        if (! $newState) {
            return new ToolResult(
                tool: 'update_idea_state',
                success: false,
                data: [],
                error: "Invalid lifecycle state: {$arguments['new_state']}",
            );
        }

        $previousState = $idea->lifecycle_state;
        $idea->update([
            'lifecycle_state' => $newState->value,
            'last_activity_at' => now(),
        ]);

        IdeaEvent::query()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'event_type' => EventType::StateChanged->value,
            'source' => EventSource::RoomAgent->value,
            'payload' => [
                'previous_state' => $previousState,
                'new_state' => $newState->value,
                'reason' => $arguments['reason'] ?? null,
            ],
        ]);

        return new ToolResult(
            tool: 'update_idea_state',
            success: true,
            data: [
                'idea_uuid' => $idea->uuid,
                'previous_state' => $previousState,
                'new_state' => $newState->value,
            ],
        );
    }
}
