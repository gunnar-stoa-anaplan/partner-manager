<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActionItemRequest extends FormRequest
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
            'description' => ['required', 'string', 'max:500'],
        ];
    }
}
