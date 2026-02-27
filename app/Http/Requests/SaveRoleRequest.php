<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRoleRequest extends FormRequest
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
        $role = $this->route('role');

        return [
            'display_name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'slug')->ignore($role),
            ],
            'type' => ['required', 'string', Rule::in(['internal', 'portal'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'landing_route' => ['nullable', 'string', 'max:100'],
            'dashboard_group' => ['nullable', 'string', Rule::in(['admin', 'sales', 'delivery'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'bypass_permissions' => ['sometimes', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(['read', 'write'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('permissions')) {
            $this->merge([
                'permissions' => array_filter($this->input('permissions', []), fn ($v) => $v !== '' && $v !== null),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug must start with a letter and contain only lowercase letters, numbers, and underscores.',
            'slug.unique' => 'This slug is already in use by another role.',
        ];
    }
}
