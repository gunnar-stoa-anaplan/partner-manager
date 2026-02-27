<?php

namespace App\Http\Controllers\IdeaRoom;

use App\Http\Controllers\Controller;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function enter(Request $request, Idea $idea): RedirectResponse
    {
        abort_unless($idea->user_id === $request->user()->id, 403);

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

        return redirect()->route('idea_room.room', $room->uuid);
    }

    public function show(Request $request, Room $room): View
    {
        abort_unless($room->user_id === $request->user()->id, 403);

        $room->load('idea.buckets.parent', 'idea.tags');

        return view('idea-room.room', ['room' => $room]);
    }
}
