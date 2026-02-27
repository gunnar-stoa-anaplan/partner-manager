<?php

namespace App\Http\Requests\IntegrationHub;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldMappingRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'source_field' => ['required', 'string', 'max:255'],
            'target_model' => ['required', 'string', 'max:255'],
            'target_field' => ['required', 'string', 'max:255'],
            'transform' => ['nullable', 'string', 'in:,lowercase,uppercase,trim,date_iso,integer,float,boolean'],
            'default_value' => ['nullable', 'string', 'max:255'],
            'is_required' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
