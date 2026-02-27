<?php

namespace App\Http\Requests\IntegrationHub;

use App\Models\ConnectorProfile;
use Illuminate\Foundation\Http\FormRequest;

class SetupConnectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        /** @var ConnectorProfile $profile */
        $profile = $this->route('profile');
        $schema = $profile->config_schema ?? [];

        foreach ($schema as $field) {
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules[] = match ($field['type'] ?? 'text') {
                'url' => 'url',
                'password', 'text', 'select' => 'string',
                default => 'string',
            };

            $fieldRules[] = 'max:1000';

            $rules["config.{$field['key']}"] = $fieldRules;
        }

        return $rules;
    }
}
