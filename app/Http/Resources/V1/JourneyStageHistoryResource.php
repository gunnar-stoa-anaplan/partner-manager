<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JourneyStageHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'journey_participant_id' => $this->journey_participant_id,
            'from_stage' => $this->from_stage,
            'to_stage' => $this->to_stage,
            'changed_by' => $this->changed_by,
            'note' => $this->note,
            'changed_at' => optional($this->changed_at)->toIso8601String(),
        ];
    }
}
