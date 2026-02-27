<?php

namespace App\Services\IdeaRoom\Orchestrator\Room;

use App\Contracts\IdeaRoom\AiGateway;
use App\Exceptions\IdeaRoom\AiGatewayException;
use App\Models\IdeaRoom\Room;
use App\Models\IdeaRoom\RoomMessage;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\ToolResult;
use InvalidArgumentException;

class RoomOrchestratorService
{
    private const MAX_TOOL_ITERATIONS = 10;

    public function __construct(
        private readonly AiGateway $ai,
        private readonly RoomToolRegistry $toolRegistry,
        private readonly RoomToolExecutor $toolExecutor,
        private readonly RoomSystemPromptBuilder $promptBuilder,
    ) {}

    /**
     * Handle a user chat message in an idea room.
     *
     * @throws AiGatewayException
     */
    public function handle(Room $room, User $user, string $message): RoomOrchestratorResult
    {
        $userMessage = RoomMessage::query()->create([
            'idea_room_id' => $room->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'content' => $message,
        ]);

        $systemPrompt = $this->promptBuilder->build($room, $user);
        $tools = $this->toolRegistry->definitions();
        $history = $this->loadHistory($room, $userMessage);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$history,
            ['role' => 'user', 'content' => $message],
        ];

        $actions = [];
        $usage = [];
        $iteration = 0;

        do {
            $response = $this->ai->chat([
                'messages' => $messages,
                'tools' => $tools,
                'temperature' => config('idea_room.room_agent.temperature', 0.7),
                'max_tokens' => config('idea_room.room_agent.max_tokens', 2048),
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
                    $result = $this->toolExecutor->execute($room, $user, $toolName, $arguments);
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

        $agentContent = $response['content'] ?? '';

        $agentMessage = RoomMessage::query()->create([
            'idea_room_id' => $room->id,
            'sender_type' => 'agent',
            'sender_id' => null,
            'content' => $agentContent,
            'metadata' => [
                'usage' => $usage,
                'actions' => collect($actions)->map(fn (ToolResult $r) => $r->toArray())->all(),
            ],
        ]);

        $room->idea->update(['last_activity_at' => now()]);
        $room->touch();

        return new RoomOrchestratorResult(
            message: $agentContent,
            actions: $actions,
            usage: $usage,
            userMessage: $userMessage,
            agentMessage: $agentMessage,
        );
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function loadHistory(Room $room, RoomMessage $excludeMessage): array
    {
        return $room->messages()
            ->where('id', '!=', $excludeMessage->id)
            ->orderBy('created_at')
            ->limit(50)
            ->get()
            ->map(fn (RoomMessage $msg) => [
                'role' => $msg->sender_type === 'user' ? 'user' : 'assistant',
                'content' => $msg->content,
            ])
            ->all();
    }
}
