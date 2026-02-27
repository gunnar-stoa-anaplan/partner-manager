<?php

namespace App\Services\IdeaRoom\Orchestrator\Tools;

use App\Models\IdeaRoom\Idea;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Contracts\ExecutesTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class SearchIdeasTool implements ExecutesTool
{
    public function execute(User $user, array $arguments): ToolResult
    {
        $query = Idea::query()
            ->where('user_id', $user->id)
            ->where(function ($q) use ($arguments) {
                $q->where('title', 'like', "%{$arguments['query']}%")
                    ->orWhere('original_text', 'like', "%{$arguments['query']}%");
            });

        if (! empty($arguments['lifecycle_state'])) {
            $query->where('lifecycle_state', $arguments['lifecycle_state']);
        }

        $limit = min($arguments['limit'] ?? 10, 25);

        $ideas = $query
            ->orderByDesc('last_activity_at')
            ->limit($limit)
            ->get(['uuid', 'title', 'lifecycle_state', 'original_text', 'last_activity_at']);

        return new ToolResult(
            tool: 'search_ideas',
            success: true,
            data: [
                'results' => $ideas->map(fn (Idea $idea) => [
                    'uuid' => $idea->uuid,
                    'title' => $idea->title,
                    'lifecycle_state' => $idea->lifecycle_state,
                    'original_text' => mb_substr($idea->original_text ?? '', 0, 200),
                    'last_activity_at' => $idea->last_activity_at?->toIso8601String(),
                ])->all(),
                'total' => $ideas->count(),
            ],
        );
    }
}
