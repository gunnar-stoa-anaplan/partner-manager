<?php

namespace App\Http\Requests\ZoomChat;

use App\Models\ZoomChatResponder;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResponderRequest extends FormRequest
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
        return [
            'enablement_session_id' => ['nullable', 'exists:enablement_sessions,id'],
            'zoom_meeting_id' => ['sometimes', 'required', 'string', 'max:255'],
            'agent_class' => ['sometimes', 'required', 'string', 'in:'.implode(',', array_keys(ZoomChatResponder::allowedAgents()))],
            'display_name' => ['sometimes', 'required', 'string', 'max:255'],
            'trigger_prefix' => ['sometimes', 'required', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'max_response_tokens' => ['sometimes', 'required', 'integer', 'min:64', 'max:4096'],
            'rate_limit_per_minute' => ['sometimes', 'required', 'integer', 'min:1', 'max:60'],
        ];
    }
}
