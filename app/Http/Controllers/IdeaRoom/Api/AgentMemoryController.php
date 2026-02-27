<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IdeaRoom\AgentMemoryResource;
use App\Models\IdeaRoom\AgentMemory;
use App\Models\IdeaRoom\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AgentMemoryController extends Controller
{
    public function index(Request $request, Room $room): AnonymousResourceCollection
    {
        abort_unless($room->user_id === $request->user()->id, 403);

        $memories = $room->memories()
            ->where('is_active', true)
            ->orderByDesc('importance')
            ->get();

        return AgentMemoryResource::collection($memories);
    }

    public function destroy(Request $request, Room $room, AgentMemory $memory): JsonResponse
    {
        abort_unless($room->user_id === $request->user()->id, 403);
        abort_unless($memory->idea_room_id === $room->id, 404);

        $memory->update(['is_active' => false]);

        return response()->json(null, 204);
    }
}
