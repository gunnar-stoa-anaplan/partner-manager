<?php

namespace App\Services\IdeaRoom\Orchestrator\Room;

use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Room\Contracts\ExecutesRoomTool;
use App\Services\IdeaRoom\Orchestrator\Room\Tools\RoomAddNoteTool;
use App\Services\IdeaRoom\Orchestrator\Room\Tools\RoomUpdateIdeaStateTool;
use App\Services\IdeaRoom\Orchestrator\Room\Tools\SaveArtifactTool;
use App\Services\IdeaRoom\Orchestrator\Room\Tools\SaveMemoryTool;
use App\Services\IdeaRoom\Orchestrator\Room\Tools\SetObjectiveTool;
use App\Services\IdeaRoom\Orchestrator\Room\Tools\UpdateArtifactTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;
use InvalidArgumentException;

class RoomToolExecutor
{
    /** @var array<string, ExecutesRoomTool> */
    private array $handlers;

    public function __construct(
        SaveArtifactTool $saveArtifact,
        UpdateArtifactTool $updateArtifact,
        RoomAddNoteTool $addNote,
        RoomUpdateIdeaStateTool $updateState,
        SaveMemoryTool $saveMemory,
        SetObjectiveTool $setObjective,
    ) {
        $this->handlers = [
            'save_artifact' => $saveArtifact,
            'update_artifact' => $updateArtifact,
            'add_note' => $addNote,
            'update_idea_state' => $updateState,
            'save_memory' => $saveMemory,
            'set_objective' => $setObjective,
        ];
    }

    public function execute(Room $room, User $user, string $toolName, array $arguments): ToolResult
    {
        $handler = $this->handlers[$toolName]
            ?? throw new InvalidArgumentException("Unknown tool: {$toolName}");

        return $handler->execute($room, $user, $arguments);
    }
}
