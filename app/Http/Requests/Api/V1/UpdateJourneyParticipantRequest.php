<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJourneyParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'external_id' => 'nullable|string|max:255',
            'last_active_at' => 'nullable|date',
            'metadata' => 'nullable|array',
        ];
    }
}
