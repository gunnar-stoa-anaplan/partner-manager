<?php

namespace App\Services\IdeaRoom\Orchestrator;

class ToolResult
{
    public function __construct(
        public readonly string $tool,
        public readonly bool $success,
        public readonly array $data,
        public readonly ?string $error = null,
    ) {}

    /**
     * @return array{tool: string, success: bool, data: array, error: string|null}
     */
    public function toArray(): array
    {
        return [
            'tool' => $this->tool,
            'success' => $this->success,
            'data' => $this->data,
            'error' => $this->error,
        ];
    }
}
