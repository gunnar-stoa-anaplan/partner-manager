<?php

namespace App\Http\Resources\IdeaRoom;

use App\Models\IdeaRoom\Idea;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Idea */
class IdeaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'original_text' => $this->original_text,
            'lifecycle_state' => $this->lifecycle_state,
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'buckets' => $this->whenLoaded('buckets', fn () => $this->buckets->map(fn ($bucket) => [
                'uuid' => $bucket->uuid,
                'name' => $bucket->name,
                'full_path' => $bucket->fullPath(),
                'color' => $bucket->color,
                'parent_bucket_uuid' => $bucket->parent?->uuid,
                'parent_bucket_name' => $bucket->parent?->name,
                'confidence' => $bucket->pivot->confidence,
                'confirmed' => $bucket->pivot->confirmed,
            ])),
            'rooms' => $this->whenLoaded('rooms', fn () => $this->rooms->map(fn ($room) => [
                'uuid' => $room->uuid,
                'name' => $room->name,
                'status' => $room->status,
            ])),
            'events' => $this->whenLoaded('events', fn () => $this->events->map(fn ($event) => [
                'event_type' => $event->event_type,
                'source' => $event->source,
                'payload' => $event->payload,
                'created_at' => $event->created_at?->toIso8601String(),
            ])),
        ];
    }
}
