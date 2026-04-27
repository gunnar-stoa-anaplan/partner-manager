<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreJourneyPendingActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'journey_participant_id' => 'nullable|integer|exists:journey_participants,id',
            'action' => 'required|string|max:64',
            'description' => 'required|string|max:2000',
            'assigned_to' => 'nullable|string|max:64',
            'due_at' => 'nullable|date',
        ];
    }
}
