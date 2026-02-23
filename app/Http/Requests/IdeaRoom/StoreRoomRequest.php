<?php

namespace App\Http\Requests\IdeaRoom;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'agent_persona' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
