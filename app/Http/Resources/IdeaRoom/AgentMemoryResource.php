<?php

namespace App\Http\Resources\IdeaRoom;

use App\Models\IdeaRoom\AgentMemory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AgentMemory */
class AgentMemoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'memory_type' => $this->memory_type,
            'content' => $this->content,
            'importance' => $this->importance,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
