<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnablementEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'category' => $this->category,
            'product_area' => $this->product_area,
            'description' => $this->description,
            'objectives' => $this->objectives,
            'prerequisites' => $this->prerequisites,
            'target_audience' => $this->target_audience,
            'duration_hours' => $this->duration_hours ? (float) $this->duration_hours : null,
            'max_participants' => $this->max_participants,
            'status' => $this->status,
            'sessions_count' => $this->sessions_count ?? $this->whenLoaded('sessions', fn () => $this->sessions->count(), 0),
            'sessions' => EnablementSessionResource::collection($this->whenLoaded('sessions')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
