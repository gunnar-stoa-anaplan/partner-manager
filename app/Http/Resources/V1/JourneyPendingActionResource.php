<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JourneyPendingActionResource extends JsonResource
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
            'action' => $this->action,
            'description' => $this->description,
            'assigned_to' => $this->assigned_to,
            'status' => $this->status,
            'due_at' => optional($this->due_at)->toIso8601String(),
            'completed_at' => optional($this->completed_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
