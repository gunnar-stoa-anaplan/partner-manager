<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'partner_id' => $this->partner_id,
            'name' => $this->name,
            'status' => $this->status,
            'acv' => $this->acv ? (float) $this->acv : null,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'percent_complete' => (int) $this->percent_complete,
            'total_sprints' => (int) $this->total_sprints,
            'current_sprint' => (int) $this->current_sprint,
            'client' => $this->client,
            'delivery_team' => $this->delivery_team,
            'initiative' => $this->initiative,
            'customer_satisfaction' => $this->customer_satisfaction,
            'is_referenceable' => (bool) $this->is_referenceable,
            'go_live_date' => $this->go_live_date?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
