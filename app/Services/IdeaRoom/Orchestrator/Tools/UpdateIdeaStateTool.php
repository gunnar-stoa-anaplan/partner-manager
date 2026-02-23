<?php

namespace App\Services\IdeaRoom\Orchestrator\Tools;

use App\Enums\IdeaRoom\EventSource;
use App\Enums\IdeaRoom\EventType;
use App\Enums\IdeaRoom\LifecycleState;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\IdeaEvent;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Contracts\ExecutesTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class UpdateIdeaStateTool implements ExecutesTool
{
    public function execute(User $user, array $arguments): ToolResult
    {
        $idea = Idea::query()
            ->where('user_id', $user->id)
            ->where('uuid', $arguments['idea_uuid'])
            ->first();

        if (! $idea) {
            return new ToolResult(
                tool: 'update_idea_state',
                success: false,
                data: [],
                error: "Idea not found with UUID: {$arguments['idea_uuid']}",
            );
        }

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
            'source' => EventSource::Orchestrator->value,
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
