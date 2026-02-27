<?php

namespace App\Services\IdeaRoom\Orchestrator\Room\Contracts;

use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

interface ExecutesRoomTool
{
    public function execute(Room $room, User $user, array $arguments): ToolResult;
}
