<?php

namespace App\Http\Requests\Api;

use App\Models\Lab;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignLabRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant' => ['required', 'string', Rule::in(array_keys(Lab::TENANTS))],
            'topic' => 'required|string|max:500',
            'workshop_context' => 'nullable|string|max:5000',
            'duration_minutes' => 'nullable|integer|min:15|max:120',
        ];
    }

    /**
     * Build the prompt string from validated input.
     */
    public function toPrompt(): string
    {
        $tenant = Lab::TENANTS[$this->validated('tenant')] ?? $this->validated('tenant');
        $topic = $this->validated('topic');
        $duration = $this->validated('duration_minutes', 45);

        $prompt = "Design a {$duration}-minute hands-on lab for the {$tenant} tenant. "
            ."The lab should focus on: {$topic}.";

        $prompt .= ' First check existing labs to see what already exists for this tenant, '
            .'then design a complete lab with checkpoints that fills a gap or teaches a new skill.';

        return $prompt;
    }
}
