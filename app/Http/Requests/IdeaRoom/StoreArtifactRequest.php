<?php

namespace App\Http\Requests\IdeaRoom;

use Illuminate\Foundation\Http\FormRequest;

class StoreArtifactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:note,outline,prd,document,checklist,file'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required_without:file', 'nullable', 'string'],
            'file' => ['required_without:content', 'nullable', 'file', 'max:10240'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Artifact type must be one of: note, outline, prd, document, checklist, file.',
            'file.max' => 'File size must not exceed 10MB.',
        ];
    }
}
