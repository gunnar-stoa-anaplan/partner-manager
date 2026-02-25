<?php

namespace App\Http\Requests\KnowledgeBase;

use Illuminate\Foundation\Http\FormRequest;

class IngestStorageDiskRequest extends FormRequest
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
            'disk' => ['required', 'string', 'max:255'],
            'prefix' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
