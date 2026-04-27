<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreJourneyAgentLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'journey_participant_id' => 'nullable|integer|exists:journey_participants,id',
            'level' => 'nullable|in:debug,info,warn,error',
            'event' => 'required|string|max:64',
            'message' => 'required|string|max:4000',
            'context' => 'nullable|array',
        ];
    }
}
