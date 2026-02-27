<?php

namespace App\Services\IdeaRoom\Orchestrator\Room\Tools;

use App\Models\IdeaRoom\Artifact;
use App\Models\IdeaRoom\ArtifactVersion;
use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Room\Contracts\ExecutesRoomTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class UpdateArtifactTool implements ExecutesRoomTool
{
    public function execute(Room $room, User $user, array $arguments): ToolResult
    {
        $artifact = Artifact::query()
            ->where('idea_id', $room->idea_id)
            ->where('uuid', $arguments['artifact_uuid'])
            ->first();

        if (! $artifact) {
            return new ToolResult(
                tool: 'update_artifact',
                success: false,
                data: [],
                error: "Artifact not found with UUID: {$arguments['artifact_uuid']}",
            );
        }

        ArtifactVersion::query()->create([
            'artifact_id' => $artifact->id,
            'version' => $artifact->version,
            'content' => $artifact->content,
            'changed_by_type' => 'agent',
            'changed_by_id' => null,
        ]);

        $newVersion = $artifact->version + 1;

        $artifact->update([
            'content' => $arguments['content'],
            'version' => $newVersion,
        ]);

        return new ToolResult(
            tool: 'update_artifact',
            success: true,
            data: [
                'artifact_uuid' => $artifact->uuid,
                'title' => $artifact->title,
                'version' => $newVersion,
            ],
        );
    }
}
