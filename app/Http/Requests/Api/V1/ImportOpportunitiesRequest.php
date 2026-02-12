<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ImportOpportunitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'snapshot_date' => 'required|date',
            'opportunities' => 'required|array|min:1|max:5000',
            'opportunities.*.opportunity_name' => 'required|string|max:255',
            'opportunities.*.partner_name' => 'required|string|max:255',
            'opportunities.*.account_name' => 'nullable|string|max:255',
            'opportunities.*.salesforce_id' => 'nullable|string|max:255',
            'opportunities.*.stage' => 'nullable|string|max:100',
            'opportunities.*.amount' => 'nullable|numeric|min:0',
            'opportunities.*.close_date' => 'nullable|date',
            'opportunities.*.type' => 'nullable|string|max:100',
            'opportunities.*.revenue_type' => 'nullable|string|in:sourced,influenced',
            'opportunities.*.product_area' => 'nullable|string|max:100',
            'opportunities.*.probability' => 'nullable|numeric|min:0|max:100',
            'opportunities.*.owner' => 'nullable|string|max:255',
            'opportunities.*.lead_source' => 'nullable|string|max:255',
        ];
    }
}
