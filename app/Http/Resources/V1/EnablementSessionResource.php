<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnablementSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->enablement_event_id,
            'title' => $this->display_title,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'all_day' => (bool) $this->all_day,
            'timezone' => $this->timezone,
            'location' => $this->location,
            'meeting_url' => $this->meeting_url,
            'instructor' => $this->instructor,
            'geography' => $this->geography,
            'region' => $this->region,
            'max_participants' => $this->effective_max_participants,
            'spots_remaining' => $this->spotsRemaining(),
            'registration_open' => $this->isRegistrationOpen(),
            'status' => $this->status,
            'registration_deadline' => $this->registration_deadline?->toIso8601String(),
            'event' => [
                'type' => $this->event?->type,
                'category' => $this->event?->category,
                'product_area' => $this->event?->product_area,
            ],
        ];
    }
}
