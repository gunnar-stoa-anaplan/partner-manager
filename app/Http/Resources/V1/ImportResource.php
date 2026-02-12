<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'source' => $this->source,
            'status' => $this->status,
            'statistics' => [
                'total_rows' => $this->total_rows,
                'successful_rows' => $this->successful_rows,
                'failed_rows' => $this->failed_rows,
                'partners_created' => $this->partners_created,
                'partners_updated' => $this->partners_updated,
                'opportunities_created' => $this->opportunities_created,
                'opportunities_updated' => $this->opportunities_updated,
                'projects_created' => $this->projects_created,
                'projects_updated' => $this->projects_updated,
                'contacts_created' => $this->contacts_created,
                'contacts_updated' => $this->contacts_updated,
            ],
            'errors' => $this->error_log,
            'summary' => $this->summary,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
