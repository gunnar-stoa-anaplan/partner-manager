<?php

namespace App\Models;

use App\Enums\ZoomChatMessageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoomChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'zoom_chat_responder_id',
        'zoom_message_id',
        'sender_name',
        'sender_email',
        'message_text',
        'response_text',
        'agent_class',
        'status',
        'error_message',
        'tokens_used',
        'processing_time_ms',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ZoomChatMessageStatus::class,
            'tokens_used' => 'integer',
            'processing_time_ms' => 'integer',
        ];
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(ZoomChatResponder::class, 'zoom_chat_responder_id');
    }
}
