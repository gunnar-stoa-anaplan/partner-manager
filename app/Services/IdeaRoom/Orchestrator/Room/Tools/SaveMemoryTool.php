<?php

namespace App\Services\IdeaRoom\Orchestrator\Room\Tools;

use App\Models\IdeaRoom\AgentMemory;
use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Room\Contracts\ExecutesRoomTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class SaveMemoryTool implements ExecutesRoomTool
{
    public function execute(Room $room, User $user, array $arguments): ToolResult
    {
        $importance = isset($arguments['importance'])
            ? max(1, min(10, (int) $arguments['importance']))
            : 5;

        $memory = AgentMemory::query()->create([
            'idea_room_id' => $room->id,
            'memory_type' => $arguments['memory_type'],
            'content' => $arguments['content'],
            'importance' => $importance,
            'is_active' => true,
        ]);

        return new ToolResult(
            tool: 'save_memory',
            success: true,
            data: [
                'memory_id' => $memory->id,
                'memory_type' => $memory->memory_type,
                'content' => $memory->content,
            ],
        );
    }
}
