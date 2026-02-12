<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'salesforce_id' => $this->salesforce_id,
            'name' => $this->name,
            'website' => $this->website,
            'industry' => $this->industry,
            'geography' => $this->geography,
            'region' => $this->region,
            'psm' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'counts' => [
                'opportunities' => $this->opportunities_count ?? $this->whenLoaded('opportunities', fn () => $this->opportunities->count(), 0),
                'projects' => $this->projects_count ?? $this->whenLoaded('projects', fn () => $this->projects->count(), 0),
                'contacts' => $this->contacts_count ?? $this->whenLoaded('contacts', fn () => $this->contacts->count(), 0),
                'leads' => $this->leads_count ?? $this->whenLoaded('leads', fn () => $this->leads->count(), 0),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
