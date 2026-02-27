<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IdeaRoom\IdeaResource;
use App\Models\IdeaRoom\Idea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IdeaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $ideas = $request->user()
            ->ideas()
            ->with('buckets.parent', 'tags')
            ->orderByDesc('last_activity_at')
            ->limit(50)
            ->get();

        return IdeaResource::collection($ideas);
    }

    public function show(Request $request, Idea $idea): IdeaResource
    {
        abort_unless($idea->user_id === $request->user()->id, 403);

        $idea->load('buckets.parent', 'tags', 'events', 'rooms');

        return new IdeaResource($idea);
    }

    public function constellation(Request $request): JsonResponse
    {
        $user = $request->user();

        $ideas = $user->ideas()
            ->with('buckets.parent', 'tags', 'outgoingEdges', 'incomingEdges')
            ->get();

        $nodes = $ideas->map(fn (Idea $idea) => [
            'uuid' => $idea->uuid,
            'title' => $idea->title,
            'lifecycle_state' => $idea->lifecycle_state,
            'last_activity_at' => $idea->last_activity_at?->toIso8601String(),
            'created_at' => $idea->created_at?->toIso8601String(),
            'original_text' => $idea->original_text,
            'tags' => $idea->tags->pluck('name'),
            'buckets' => $idea->buckets->map(fn ($b) => [
                'uuid' => $b->uuid,
                'name' => $b->name,
                'full_path' => $b->fullPath(),
                'color' => $b->color,
                'parent_bucket_name' => $b->parent?->name,
                'confidence' => $b->pivot->confidence,
            ]),
        ]);

        $edges = $ideas->flatMap(function (Idea $idea) use ($ideas) {
            $ideaUuids = $ideas->pluck('uuid', 'id');

            return $idea->outgoingEdges->map(fn ($edge) => [
                'source' => $ideaUuids[$edge->source_idea_id] ?? null,
                'target' => $ideaUuids[$edge->target_idea_id] ?? null,
                'edge_type' => $edge->edge_type,
                'confidence' => $edge->confidence,
            ])->filter(fn ($e) => $e['source'] && $e['target']);
        })->values();

        return response()->json([
            'nodes' => $nodes,
            'edges' => $edges,
        ]);
    }
}
