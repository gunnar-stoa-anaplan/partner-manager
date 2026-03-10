<?php

namespace App\Http\Requests;

use App\Models\EnablementGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertEnablementGoalRequest extends FormRequest
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
            'category' => ['required', 'string', Rule::in(EnablementGoal::categoryKeys())],
            'target' => ['required', 'integer', 'min:0'],
            'baseline' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.in' => 'Invalid enablement goal category.',
        ];
    }
}
