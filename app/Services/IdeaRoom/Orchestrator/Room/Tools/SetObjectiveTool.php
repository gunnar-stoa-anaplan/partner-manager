<?php

namespace App\Services\IdeaRoom\Orchestrator\Room\Tools;

use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Room\Contracts\ExecutesRoomTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class SetObjectiveTool implements ExecutesRoomTool
{
    public function execute(Room $room, User $user, array $arguments): ToolResult
    {
        $config = $room->agent_config ?? [];
        $config['objective'] = $arguments['objective'];

        $room->update(['agent_config' => $config]);

        return new ToolResult(
            tool: 'set_objective',
            success: true,
            data: [
                'objective' => $arguments['objective'],
            ],
        );
    }
}
