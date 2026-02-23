<?php

namespace App\Http\Resources\IdeaRoom;

use App\Models\IdeaRoom\RoomMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RoomMessage */
class RoomMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'sender_type' => $this->sender_type,
            'sender_id' => $this->sender_id,
            'content' => $this->content,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
