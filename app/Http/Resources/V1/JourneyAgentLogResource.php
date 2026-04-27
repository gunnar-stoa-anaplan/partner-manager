<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JourneyAgentLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'journey_id' => $this->journey_id,
            'journey_participant_id' => $this->journey_participant_id,
            'level' => $this->level,
            'event' => $this->event,
            'message' => $this->message,
            'context' => $this->context,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
