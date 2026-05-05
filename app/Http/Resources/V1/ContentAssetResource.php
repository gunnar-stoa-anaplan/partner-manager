<?php

namespace App\Http\Resources\V1;

use App\Models\ContentAsset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContentAsset
 */
class ContentAssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'type_label' => $this->typeLabel(),
            'lob' => $this->lob,
            'application' => $this->application,
            'persona' => $this->persona,
            'url' => $this->url,
            'owner_name' => $this->owner_name,
            'secondary_owner_name' => $this->secondary_owner_name,
            'status' => $this->status,
            'core_app' => $this->core_app,
            'launch_year' => $this->launch_year,
            'is_priority_need' => $this->is_priority_need,
            'is_launching_in_progress' => $this->is_launching_in_progress,
            'validated_at' => $this->validated_at?->toIso8601String(),
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
