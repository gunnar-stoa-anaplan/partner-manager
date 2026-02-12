<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EnablementEventResource;
use App\Http\Resources\V1\EnablementSessionResource;
use App\Models\EnablementEvent;
use App\Models\EnablementSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnablementController extends Controller
{
    public function calendar(Request $request): JsonResponse
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
            'type' => 'nullable|string',
            'geography' => 'nullable|string',
            'product_area' => 'nullable|string',
        ]);

        $query = EnablementSession::with(['event'])
            ->whereHas('event', fn ($q) => $q->where('status', 'published'))
            ->where('status', '!=', 'cancelled');

        if ($request->filled('start')) {
            $query->where('start_at', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->where('end_at', '<=', $request->end);
        }
        if ($request->filled('type')) {
            $query->whereHas('event', fn ($q) => $q->where('type', $request->type));
        }
        if ($request->filled('geography')) {
            $query->where('geography', $request->geography);
        }
        if ($request->filled('product_area')) {
            $query->whereHas('event', fn ($q) => $q->where('product_area', $request->product_area));
        }

        $sessions = $query->orderBy('start_at')->get();

        return response()->json([
            'data' => EnablementSessionResource::collection($sessions),
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $query = EnablementEvent::withCount('sessions')
            ->where('status', 'published');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('product_area')) {
            $query->where('product_area', $request->product_area);
        }

        $perPage = min((int) ($request->per_page ?? 25), 100);
        $events = $query->orderBy('title')->paginate($perPage);

        return EnablementEventResource::collection($events)->response();
    }

    public function show(int $id): JsonResponse
    {
        $event = EnablementEvent::with(['sessions' => fn ($q) => $q->orderBy('start_at')])
            ->where('status', 'published')
            ->findOrFail($id);

        return response()->json([
            'data' => new EnablementEventResource($event),
        ]);
    }
}
