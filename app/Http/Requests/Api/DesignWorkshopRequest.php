<?php

namespace App\Http\Requests\Api;

use App\Models\EnablementEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignWorkshopRequest extends FormRequest
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
        $productAreas = collect(EnablementEvent::PRODUCT_AREAS)->flatMap(fn ($areas) => array_keys($areas))->all();

        return [
            'product_area' => ['required', 'string', Rule::in($productAreas)],
            'type' => ['required', 'string', Rule::in(array_keys(EnablementEvent::TYPES))],
            'topic' => 'nullable|string|max:500',
            'audience' => 'nullable|string|max:255',
        ];
    }

    /**
     * Build the prompt string from validated input.
     */
    public function toPrompt(): string
    {
        $product = $this->validated('product_area');
        $type = EnablementEvent::TYPES[$this->validated('type')] ?? $this->validated('type');
        $audience = $this->validated('audience', 'Model Builders');
        $topic = $this->validated('topic');

        $prompt = "Design a {$type} for the {$product} application targeting {$audience}.";

        if ($topic) {
            $prompt .= " The workshop should focus on: {$topic}.";
        }

        $prompt .= ' First check the existing catalog to see what already exists for this product area,'
            .' then design a complete workshop package that fills a gap or improves on existing offerings.';

        return $prompt;
    }
}
