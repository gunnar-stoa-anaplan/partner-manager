<?php

namespace App\Services\IdeaRoom\Orchestrator\Room\Tools;

use App\Models\IdeaRoom\Artifact;
use App\Models\IdeaRoom\ArtifactVersion;
use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Room\Contracts\ExecutesRoomTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class SaveArtifactTool implements ExecutesRoomTool
{
    public function execute(Room $room, User $user, array $arguments): ToolResult
    {
        $artifact = Artifact::query()->create([
            'idea_id' => $room->idea_id,
            'idea_room_id' => $room->id,
            'user_id' => $user->id,
            'type' => $arguments['type'],
            'title' => $arguments['title'],
            'content' => $arguments['content'],
            'version' => 1,
        ]);

        ArtifactVersion::query()->create([
            'artifact_id' => $artifact->id,
            'version' => 1,
            'content' => $arguments['content'],
            'changed_by_type' => 'agent',
            'changed_by_id' => null,
        ]);

        return new ToolResult(
            tool: 'save_artifact',
            success: true,
            data: [
                'artifact_uuid' => $artifact->uuid,
                'title' => $artifact->title,
                'type' => $artifact->type,
            ],
        );
    }
}
