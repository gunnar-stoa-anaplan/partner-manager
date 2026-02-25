<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AuthLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
            'abilities' => 'sometimes|array|min:1',
            'abilities.*' => 'string|in:imports:write,imports:read,partners:read,enablement:read',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_name.required' => 'A device or integration name is required to identify this token.',
        ];
    }
}
