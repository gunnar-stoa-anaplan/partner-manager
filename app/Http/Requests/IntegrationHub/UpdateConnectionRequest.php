<?php

namespace App\Http\Requests\IntegrationHub;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConnectionRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:csv_upload,rest_api,salesforce,webhook,database'],
            'direction' => ['sometimes', 'string', 'in:import,export,both'],
            'config' => ['nullable', 'json'],
            'auth_config' => ['nullable', 'json'],
            'status' => ['sometimes', 'string', 'in:active,inactive,error'],
        ];
    }
}
