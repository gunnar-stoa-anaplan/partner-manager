<?php

namespace App\Http\Requests\IntegrationHub;

use Illuminate\Foundation\Http\FormRequest;

class ApplyTemplateRequest extends FormRequest
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
            'connection_id' => ['required', 'exists:integration_connections,id'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fields.required' => 'Select at least one field to apply.',
            'fields.min' => 'Select at least one field to apply.',
        ];
    }
}
