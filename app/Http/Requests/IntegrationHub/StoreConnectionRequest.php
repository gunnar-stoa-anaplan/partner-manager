<?php

namespace App\Http\Requests\IntegrationHub;

use Illuminate\Foundation\Http\FormRequest;

class StoreConnectionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:csv_upload,rest_api,salesforce,webhook,database'],
            'direction' => ['required', 'string', 'in:import,export,both'],
            'config' => ['nullable', 'json'],
            'auth_config' => ['nullable', 'json'],
            'status' => ['sometimes', 'string', 'in:active,inactive,error'],
        ];
    }
}
