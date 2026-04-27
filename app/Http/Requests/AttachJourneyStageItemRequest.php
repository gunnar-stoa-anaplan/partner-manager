<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachJourneyStageItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage_number' => 'required|integer|min:1|max:5',
            'item_type' => 'nullable|in:asset,journey',
            'item_id' => 'required|integer|min:1',
            'required' => 'nullable|boolean',
        ];
    }
}
