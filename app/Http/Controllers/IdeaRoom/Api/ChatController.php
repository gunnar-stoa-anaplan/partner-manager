<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Exceptions\IdeaRoom\AiGatewayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\IdeaRoom\StoreChatRequest;
use App\Http\Resources\IdeaRoom\ChatResponseResource;
use App\Services\IdeaRoom\Orchestrator\OrchestratorService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct(
        private readonly OrchestratorService $orchestrator,
    ) {}

    public function store(StoreChatRequest $request): JsonResponse
    {
        try {
            $result = $this->orchestrator->handle(
                user: $request->user(),
                message: $request->validated('message'),
                conversationHistory: $request->validated('conversation_history', []),
            );

            return response()->json(
                new ChatResponseResource($result),
            );
        } catch (AiGatewayException) {
            return response()->json([
                'error' => 'ai_unavailable',
                'message' => 'The AI service is temporarily unavailable. Please try again in a moment.',
            ], 503);
        }
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => []]);
    }
}
