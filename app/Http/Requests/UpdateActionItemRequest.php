<?php

namespace App\Http\Requests;

use App\Models\WeeklyFocusItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActionItemRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(array_keys(WeeklyFocusItem::STATUSES))],
        ];
    }
}
