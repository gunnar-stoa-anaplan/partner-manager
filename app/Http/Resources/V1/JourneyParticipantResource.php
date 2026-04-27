<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JourneyParticipantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'journey_id' => $this->journey_id,
            'name' => $this->name,
            'email' => $this->email,
            'external_id' => $this->external_id,
            'current_stage' => $this->current_stage,
            'enrolled_at' => optional($this->enrolled_at)->toIso8601String(),
            'last_active_at' => optional($this->last_active_at)->toIso8601String(),
            'metadata' => $this->metadata,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
