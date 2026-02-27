<?php

namespace App\Services\IdeaRoom\Orchestrator;

use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Contracts\ExecutesTool;
use App\Services\IdeaRoom\Orchestrator\Tools\AddNoteTool;
use App\Services\IdeaRoom\Orchestrator\Tools\CreateIdeaTool;
use App\Services\IdeaRoom\Orchestrator\Tools\ProposeBucketsTool;
use App\Services\IdeaRoom\Orchestrator\Tools\SearchIdeasTool;
use App\Services\IdeaRoom\Orchestrator\Tools\SuggestTriageTool;
use App\Services\IdeaRoom\Orchestrator\Tools\UpdateIdeaStateTool;
use InvalidArgumentException;

class ToolExecutor
{
    /** @var array<string, ExecutesTool> */
    private array $handlers;

    public function __construct(
        CreateIdeaTool $createIdea,
        ProposeBucketsTool $proposeBuckets,
        UpdateIdeaStateTool $updateState,
        SearchIdeasTool $searchIdeas,
        AddNoteTool $addNote,
        SuggestTriageTool $suggestTriage,
    ) {
        $this->handlers = [
            'create_idea' => $createIdea,
            'propose_buckets' => $proposeBuckets,
            'update_idea_state' => $updateState,
            'search_ideas' => $searchIdeas,
            'add_note' => $addNote,
            'suggest_triage' => $suggestTriage,
        ];
    }

    public function execute(User $user, string $toolName, array $arguments): ToolResult
    {
        $handler = $this->handlers[$toolName]
            ?? throw new InvalidArgumentException("Unknown tool: {$toolName}");

        return $handler->execute($user, $arguments);
    }
}
