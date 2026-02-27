<?php

namespace App\Services\IdeaRoom\Orchestrator;

use App\Contracts\IdeaRoom\AiGateway;
use App\Exceptions\IdeaRoom\AiGatewayException;
use App\Models\User;
use InvalidArgumentException;

class OrchestratorService
{
    private const MAX_TOOL_ITERATIONS = 10;

    public function __construct(
        private readonly AiGateway $ai,
        private readonly ToolRegistry $toolRegistry,
        private readonly ToolExecutor $toolExecutor,
        private readonly SystemPromptBuilder $promptBuilder,
    ) {}

    /**
     * Handle a user chat message through the orchestrator.
     *
     * @param  array<int, array{role: string, content: string}>  $conversationHistory
     *
     * @throws AiGatewayException
     */
    public function handle(User $user, string $message, array $conversationHistory = []): OrchestratorResult
    {
        $systemPrompt = $this->promptBuilder->build($user);
        $tools = $this->toolRegistry->definitions();

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$conversationHistory,
            ['role' => 'user', 'content' => $message],
        ];

        $actions = [];
        $usage = [];
        $iteration = 0;

        do {
            $response = $this->ai->chat([
                'messages' => $messages,
                'tools' => $tools,
                'temperature' => config('idea_room.orchestrator.temperature'),
                'max_tokens' => config('idea_room.orchestrator.max_tokens'),
            ]);

            $usage = $response['usage'];

            if (empty($response['tool_calls'])) {
                break;
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $response['content'],
                'tool_calls' => $response['tool_calls'],
            ];

            foreach ($response['tool_calls'] as $toolCall) {
                $toolName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'], true) ?? [];

                try {
                    $result = $this->toolExecutor->execute($user, $toolName, $arguments);
                } catch (InvalidArgumentException $e) {
                    $result = new ToolResult(
                        tool: $toolName,
                        success: false,
                        data: [],
                        error: $e->getMessage(),
                    );
                }

                $actions[] = $result;

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'content' => json_encode($result->toArray()),
                ];
            }

            $iteration++;
        } while ($iteration < self::MAX_TOOL_ITERATIONS);

        return new OrchestratorResult(
            message: $response['content'] ?? '',
            actions: $actions,
            usage: $usage,
        );
    }
}
