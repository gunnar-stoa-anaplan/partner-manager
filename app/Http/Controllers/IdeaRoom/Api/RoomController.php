<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IdeaRoom\StoreRoomRequest;
use App\Http\Requests\IdeaRoom\UpdateRoomRequest;
use App\Http\Resources\IdeaRoom\RoomResource;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoomController extends Controller
{
    public function store(StoreRoomRequest $request, Idea $idea): JsonResponse
    {
        abort_unless($idea->user_id === $request->user()->id, 403);

        $room = Room::query()->create([
            'idea_id' => $idea->id,
            'user_id' => $request->user()->id,
            'name' => $request->validated('name', $idea->title),
            'status' => 'active',
            'agent_persona' => $request->validated('agent_persona', 'general'),
        ]);

        return response()->json(new RoomResource($room), 201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $rooms = $request->user()
            ->ideaRooms()
            ->with(['idea.buckets.parent'])
            ->withCount('messages', 'artifacts')
            ->orderByDesc('updated_at')
            ->get();

        return RoomResource::collection($rooms);
    }

    public function show(Request $request, Room $room): RoomResource
    {
        abort_unless($room->user_id === $request->user()->id, 403);

        $room->load('idea');
        $room->loadCount('messages', 'artifacts');

        return new RoomResource($room);
    }

    public function update(UpdateRoomRequest $request, Room $room): RoomResource
    {
        abort_unless($room->user_id === $request->user()->id, 403);

        $room->update($request->validated());

        return new RoomResource($room);
    }
}
