<?php

namespace App\Services\IdeaRoom\Orchestrator\Room\Tools;

use App\Enums\IdeaRoom\EventSource;
use App\Enums\IdeaRoom\EventType;
use App\Models\IdeaRoom\IdeaEvent;
use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Room\Contracts\ExecutesRoomTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class RoomAddNoteTool implements ExecutesRoomTool
{
    public function execute(Room $room, User $user, array $arguments): ToolResult
    {
        $idea = $room->idea;

        IdeaEvent::query()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'event_type' => EventType::NoteAdded->value,
            'source' => EventSource::RoomAgent->value,
            'payload' => [
                'note' => $arguments['note'],
            ],
        ]);

        $idea->update(['last_activity_at' => now()]);

        return new ToolResult(
            tool: 'add_note',
            success: true,
            data: [
                'idea_uuid' => $idea->uuid,
                'idea_title' => $idea->title,
            ],
        );
    }
}
