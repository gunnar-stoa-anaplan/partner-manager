<?php

namespace App\Services\IdeaRoom\Orchestrator\Tools;

use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Room;
use App\Models\User;
use App\Services\IdeaRoom\Orchestrator\Contracts\ExecutesTool;
use App\Services\IdeaRoom\Orchestrator\ToolResult;

class SuggestTriageTool implements ExecutesTool
{
    public function execute(User $user, array $arguments): ToolResult
    {
        $rawSuggestions = $arguments['suggestions'] ?? [];

        $ideaUuids = array_column($rawSuggestions, 'idea_uuid');
        $ideas = Idea::query()
            ->where('user_id', $user->id)
            ->whereIn('uuid', $ideaUuids)
            ->get(['uuid', 'title'])
            ->keyBy('uuid');

        $suggestedRoomUuids = array_filter(array_column($rawSuggestions, 'suggested_room_uuid'));
        $suggestedRooms = ! empty($suggestedRoomUuids)
            ? Room::query()
                ->where('user_id', $user->id)
                ->whereIn('uuid', $suggestedRoomUuids)
                ->get(['uuid', 'name'])
                ->keyBy('uuid')
            : collect();

        $suggestions = array_map(function ($s) use ($ideas, $suggestedRooms) {
            $idea = $ideas->get($s['idea_uuid']);
            $suggestedRoom = isset($s['suggested_room_uuid'])
                ? $suggestedRooms->get($s['suggested_room_uuid'])
                : null;

            return [
                'idea_uuid' => $s['idea_uuid'],
                'emoji' => $s['emoji'],
                'idea_title' => $idea?->title ?? $s['idea_uuid'],
                'suggested_room_uuid' => $suggestedRoom?->uuid,
                'suggested_room_name' => $suggestedRoom?->name,
            ];
        }, $rawSuggestions);

        $rooms = Room::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['idea.buckets.parent'])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        $targets = $rooms->map(function (Room $room) {
            $idea = $room->idea;
            $bucketNames = $idea->buckets->map(function ($bucket) {
                return $bucket->parent
                    ? $bucket->parent->name.' > '.$bucket->name
                    : $bucket->name;
            })->all();

            return [
                'type' => 'room',
                'room_uuid' => $room->uuid,
                'room_name' => $room->name,
                'idea_uuid' => $idea->uuid,
                'idea_title' => $idea->title,
                'buckets' => $bucketNames,
                'last_activity' => $room->updated_at?->toIso8601String(),
            ];
        })->all();

        $buckets = $user->ideaBuckets()
            ->withCount('ideas')
            ->with('parent')
            ->orderByDesc('ideas_count')
            ->limit(10)
            ->get();

        $bucketTargets = $buckets->map(function ($bucket) {
            return [
                'type' => 'bucket',
                'name' => $bucket->parent
                    ? $bucket->parent->name.' > '.$bucket->name
                    : $bucket->name,
                'bucket_uuid' => $bucket->uuid,
                'idea_count' => $bucket->ideas_count,
            ];
        })->all();

        return new ToolResult(
            tool: 'suggest_triage',
            success: true,
            data: [
                'suggestions' => $suggestions,
                'room_targets' => $targets,
                'bucket_targets' => $bucketTargets,
            ],
        );
    }
}
