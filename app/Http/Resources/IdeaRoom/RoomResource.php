<?php

namespace App\Http\Resources\IdeaRoom;

use App\Models\IdeaRoom\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Room */
class RoomResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status,
            'agent_persona' => $this->agent_persona,
            'agent_config' => $this->agent_config,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'idea' => $this->whenLoaded('idea', fn () => [
                'uuid' => $this->idea->uuid,
                'title' => $this->idea->title,
                'lifecycle_state' => $this->idea->lifecycle_state,
                'original_text' => $this->idea->original_text,
            ]),
            'root_category' => $this->whenLoaded('idea', function (): ?string {
                $bucket = $this->idea->buckets->first();

                if (! $bucket) {
                    return null;
                }

                return $bucket->parent ? $bucket->parent->name : $bucket->name;
            }),
            'messages_count' => $this->whenCounted('messages'),
            'artifacts_count' => $this->whenCounted('artifacts'),
        ];
    }
}
