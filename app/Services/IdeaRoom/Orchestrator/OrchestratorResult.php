<?php

namespace App\Services\IdeaRoom\Orchestrator;

class OrchestratorResult
{
    /**
     * @param  ToolResult[]  $actions
     * @param  array{prompt_tokens?: int, completion_tokens?: int, total_tokens?: int}  $usage
     */
    public function __construct(
        public readonly string $message,
        public readonly array $actions,
        public readonly array $usage,
    ) {}
}
