<?php

namespace App\Http\Requests\Api\V1;

use App\Services\DataIngestionService;
use Illuminate\Foundation\Http\FormRequest;

class IngestRequest extends FormRequest
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
            'rows' => ['required', 'array', 'min:1', 'max:5000'],
            'rows.*' => ['required', 'array'],
            'connection_id' => ['nullable', 'integer', 'exists:integration_connections,id'],
            'snapshot_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $model = $this->route('model');
            if (! in_array($model, DataIngestionService::allowedModels())) {
                $validator->errors()->add('model', "Invalid model: {$model}. Allowed: ".implode(', ', DataIngestionService::allowedModels()));
            }

            if ($model === 'opportunities' && ! $this->input('snapshot_date')) {
                $validator->errors()->add('snapshot_date', 'snapshot_date is required for opportunity imports.');
            }
        });
    }
}
