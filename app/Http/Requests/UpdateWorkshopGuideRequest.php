<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkshopGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'navigation' => ['required', 'json'],
            'enablement_event_id' => ['nullable', 'exists:enablement_events,id'],
            'product_area' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'requires_auth' => ['sometimes', 'boolean'],
            'estimated_duration_hours' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ];
    }
}
