<?php

namespace App\Http\Resources\IdeaRoom;

use App\Models\IdeaRoom\Artifact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Artifact */
class ArtifactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->when($this->type !== 'file', $this->content),
            'file_mime' => $this->file_mime,
            'file_size' => $this->file_size,
            'version' => $this->version,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'versions' => $this->whenLoaded('versions', fn () => $this->versions->map(fn ($v) => [
                'version' => $v->version,
                'changed_by_type' => $v->changed_by_type,
                'created_at' => $v->created_at?->toIso8601String(),
            ])),
        ];
    }
}
