<?php

namespace App\Http\Requests\IdeaRoom;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
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
            'message' => ['required', 'string', 'max:10000'],
            'conversation_history' => ['sometimes', 'array', 'max:50'],
            'conversation_history.*.role' => ['required_with:conversation_history', 'string', 'in:user,assistant'],
            'conversation_history.*.content' => ['required_with:conversation_history', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Please enter a message.',
            'message.max' => 'Your message is too long. Please keep it under 10,000 characters.',
            'conversation_history.max' => 'Conversation history is too long.',
        ];
    }
}
