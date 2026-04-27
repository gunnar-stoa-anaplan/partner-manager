<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreJourneyParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'external_id' => 'nullable|string|max:255',
            'current_stage' => 'nullable|integer|min:1|max:5',
            'metadata' => 'nullable|array',
        ];
    }
}
