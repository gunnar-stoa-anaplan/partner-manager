<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ImportContactsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_name' => 'required|string|max:255',
            'contacts' => 'required|array|min:1|max:5000',
            'contacts.*.first_name' => 'required|string|max:255',
            'contacts.*.last_name' => 'required|string|max:255',
            'contacts.*.email' => 'required|email|max:255',
            'contacts.*.title' => 'nullable|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:50',
        ];
    }
}
