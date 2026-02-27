<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $ideaCounts = $user->ideas()
            ->selectRaw('lifecycle_state, count(*) as count')
            ->groupBy('lifecycle_state')
            ->pluck('count', 'lifecycle_state');

        $activeRooms = $user->ideaRooms()
            ->where('status', 'active')
            ->count();

        $sparkUnrouted = $user->ideas()
            ->where('lifecycle_state', 'spark')
            ->whereDoesntHave('rooms', function ($q) {
                $q->where('status', 'active');
            })
            ->count();

        $recentRoom = $user->ideaRooms()
            ->where('status', 'active')
            ->with('idea:id,uuid,title')
            ->orderByDesc('updated_at')
            ->first();

        return response()->json([
            'active_rooms' => $activeRooms,
            'spark_unrouted' => $sparkUnrouted,
            'growing' => $ideaCounts['growing'] ?? 0,
            'developed' => $ideaCounts['developed'] ?? 0,
            'total_ideas' => $user->ideas()->count(),
            'recent_room' => $recentRoom ? [
                'uuid' => $recentRoom->uuid,
                'name' => $recentRoom->name,
                'idea_uuid' => $recentRoom->idea?->uuid,
                'idea_title' => $recentRoom->idea?->title,
            ] : null,
        ]);
    }
}
