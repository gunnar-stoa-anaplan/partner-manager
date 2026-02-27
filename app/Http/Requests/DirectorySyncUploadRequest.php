<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DirectorySyncUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->bypassesPermissions()
            || in_array($this->user()?->role?->slug, ['coe']);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'connection_id' => ['required', 'exists:integration_connections,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }
}
