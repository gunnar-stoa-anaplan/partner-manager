<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'salesforce_id' => $this->salesforce_id,
            'opportunity_name' => $this->opportunity_name,
            'account_name' => $this->account_name,
            'partner_id' => $this->partner_id,
            'stage' => $this->stage,
            'amount' => (float) $this->amount,
            'close_date' => $this->close_date?->toDateString(),
            'fiscal_quarter' => $this->fiscal_quarter,
            'fiscal_year' => $this->fiscal_year,
            'type' => $this->type,
            'revenue_type' => $this->revenue_type,
            'product_area' => $this->product_area,
            'probability' => (float) $this->probability,
            'owner' => $this->owner,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
