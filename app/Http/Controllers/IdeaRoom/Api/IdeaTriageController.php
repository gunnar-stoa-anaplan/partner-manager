<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Http\Controllers\Controller;
use App\Models\IdeaRoom\Edge;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdeaTriageController extends Controller
{
    public function routeToRoom(Request $request, Idea $idea): JsonResponse
    {
        abort_unless($idea->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'room_uuid' => ['required_without:create_new', 'string'],
            'create_new' => ['required_without:room_uuid', 'boolean'],
        ]);

        if (! empty($validated['create_new'])) {
            $room = Room::query()->create([
                'idea_id' => $idea->id,
                'user_id' => $request->user()->id,
                'name' => $idea->title,
                'status' => 'active',
                'agent_persona' => 'general',
            ]);

            return response()->json([
                'routed' => true,
                'room_uuid' => $room->uuid,
                'room_name' => $room->name,
                'created_new' => true,
            ]);
        }

        $targetRoom = Room::query()
            ->where('uuid', $validated['room_uuid'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($targetRoom->idea_id !== $idea->id) {
            Edge::query()->firstOrCreate(
                [
                    'source_idea_id' => $idea->id,
                    'target_idea_id' => $targetRoom->idea_id,
                ],
                [
                    'edge_type' => 'related',
                    'proposed_by' => 'user',
                    'confidence' => 0.80,
                ]
            );
        }

        $room = $idea->rooms()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (! $room) {
            $room = Room::query()->create([
                'idea_id' => $idea->id,
                'user_id' => $request->user()->id,
                'name' => $idea->title,
                'status' => 'active',
                'agent_persona' => 'general',
            ]);
        }

        return response()->json([
            'routed' => true,
            'room_uuid' => $room->uuid,
            'room_name' => $room->name,
            'created_new' => false,
            'linked_to' => $targetRoom->uuid,
        ]);
    }
}
