<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ImportProjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_name' => 'required|string|max:255',
            'projects' => 'required|array|min:1|max:1000',
            'projects.*.name' => 'required|string|max:255',
            'projects.*.status' => 'nullable|string|max:50',
            'projects.*.acv' => 'nullable|numeric|min:0',
            'projects.*.start_date' => 'nullable|date',
            'projects.*.end_date' => 'nullable|date',
            'projects.*.percent_complete' => 'nullable|integer|min:0|max:100',
            'projects.*.client' => 'nullable|string|max:255',
            'projects.*.delivery_team' => 'nullable|string|max:255',
            'projects.*.initiative' => 'nullable|string|max:255',
            'projects.*.budget' => 'nullable|numeric|min:0',
        ];
    }
}
