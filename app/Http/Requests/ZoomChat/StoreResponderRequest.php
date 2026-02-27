<?php

namespace App\Http\Requests\ZoomChat;

use App\Models\ZoomChatResponder;
use Illuminate\Foundation\Http\FormRequest;

class StoreResponderRequest extends FormRequest
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
            'zoom_meeting_id' => ['required', 'string', 'max:255'],
            'agent_class' => ['required', 'string', 'in:'.implode(',', array_keys(ZoomChatResponder::allowedAgents()))],
            'display_name' => ['required', 'string', 'max:255'],
            'trigger_prefix' => ['required', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'max_response_tokens' => ['required', 'integer', 'min:64', 'max:4096'],
            'rate_limit_per_minute' => ['required', 'integer', 'min:1', 'max:60'],
        ];
    }
}
