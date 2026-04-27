<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class TransitionJourneyStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_stage' => 'required|integer|min:1|max:5',
            'changed_by' => 'required|string|max:64',
            'note' => 'nullable|string|max:2000',
        ];
    }
}
