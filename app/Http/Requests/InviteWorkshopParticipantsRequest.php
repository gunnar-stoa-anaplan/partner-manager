<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteWorkshopParticipantsRequest extends FormRequest
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
            'emails' => ['required', 'string', 'max:10000'],
        ];
    }
}
