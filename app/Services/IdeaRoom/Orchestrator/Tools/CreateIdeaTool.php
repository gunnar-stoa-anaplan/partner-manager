<?php

namespace App\Services\IdeaRoom\Orchestrator\Tools;

use App\Enums\IdeaRoom\EventSource;
use App\Enums\IdeaRoom\EventType;
use App\Enums\IdeaRoom\LifecycleState;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\IdeaEvent;
use App\Models\IdeaRoom\Tag;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Contracts\ExecutesTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class CreateIdeaTool implements ExecutesTool
{
    public function execute(User $user, array $arguments): ToolResult
    {
        $idea = Idea::query()->create([
            'user_id' => $user->id,
            'title' => $arguments['title'],
            'original_text' => $arguments['original_text'],
            'lifecycle_state' => LifecycleState::Spark->value,
        ]);

        IdeaEvent::query()->create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'event_type' => EventType::Created->value,
            'source' => EventSource::Orchestrator->value,
            'payload' => [
                'title' => $idea->title,
                'original_text' => $idea->original_text,
            ],
        ]);

        if (! empty($arguments['tags'])) {
            foreach ($arguments['tags'] as $tagName) {
                $tag = Tag::query()->firstOrCreate(
                    ['user_id' => $user->id, 'name' => strtolower(trim($tagName))],
                );
                $idea->tags()->attach($tag->id, [
                    'source' => EventSource::Orchestrator->value,
                    'created_at' => now(),
                ]);
            }
        }

        return new ToolResult(
            tool: 'create_idea',
            success: true,
            data: [
                'idea_uuid' => $idea->uuid,
                'title' => $idea->title,
                'lifecycle_state' => $idea->lifecycle_state,
                'tags' => $idea->tags()->pluck('name')->all(),
            ],
        );
    }
}
