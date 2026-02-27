<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Exceptions\IdeaRoom\AiGatewayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\IdeaRoom\StoreRoomMessageRequest;
use App\Http\Resources\IdeaRoom\RoomChatResponseResource;
use App\Http\Resources\IdeaRoom\RoomMessageResource;
use App\Models\IdeaRoom\Room;
use App\Services\IdeaRoom\Orchestrator\Room\RoomOrchestratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoomMessageController extends Controller
{
    public function __construct(
        private readonly RoomOrchestratorService $orchestrator,
    ) {}

    public function index(Request $request, Room $room): AnonymousResourceCollection
    {
        abort_unless($room->user_id === $request->user()->id, 403);

        $messages = $room->messages()
            ->orderBy('created_at')
            ->get();

        return RoomMessageResource::collection($messages);
    }

    public function store(StoreRoomMessageRequest $request, Room $room): JsonResponse
    {
        abort_unless($room->user_id === $request->user()->id, 403);

        try {
            $result = $this->orchestrator->handle(
                room: $room,
                user: $request->user(),
                message: $request->validated('message'),
            );

            return response()->json(new RoomChatResponseResource($result));
        } catch (AiGatewayException) {
            return response()->json([
                'error' => 'ai_unavailable',
                'message' => 'The AI service is temporarily unavailable. Please try again in a moment.',
            ], 503);
        }
    }
}
