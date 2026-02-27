<?php

namespace App\Services\IdeaRoom\Orchestrator\Tools;

use App\Enums\IdeaRoom\EventSource;
use App\Enums\IdeaRoom\EventType;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\IdeaEvent;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Contracts\ExecutesTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class AddNoteTool implements ExecutesTool
{
    public function execute(User $user, array $arguments): ToolResult
    {
        $idea = Idea::query()
            ->where('user_id', $user->id)
            ->where('uuid', $arguments['idea_uuid'])
            ->first();

        if (! $idea) {
            return new ToolResult(
                tool: 'add_note',
                success: false,
                data: [],
                error: "Idea not found with UUID: {$arguments['idea_uuid']}",
            );
        }

        IdeaEvent::query()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'event_type' => EventType::NoteAdded->value,
            'source' => EventSource::Orchestrator->value,
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
