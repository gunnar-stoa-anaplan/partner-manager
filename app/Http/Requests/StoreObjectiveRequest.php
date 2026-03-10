<?php

namespace App\Http\Requests;

use App\Models\PsmObjective;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreObjectiveRequest extends FormRequest
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
            'partner_id' => ['required', 'integer', 'exists:partners,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', Rule::in(array_keys(PsmObjective::CATEGORIES))],
            'metric_type' => ['required', 'string', Rule::in(array_keys(PsmObjective::METRIC_TYPES))],
            'metric_config' => ['nullable', 'array'],
            'target_value' => ['required', 'numeric', 'min:0.01'],
            'target_date' => ['required', 'date', 'after:today'],
            'source_signal' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target_value.min' => 'The target must be greater than zero.',
            'target_date.after' => 'The target date must be in the future.',
        ];
    }
}
