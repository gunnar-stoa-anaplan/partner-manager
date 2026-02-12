<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'notification_template_id', 'event_type', 'recipient_type',
        'recipient_id', 'channel', 'subject', 'body', 'status',
        'sent_at', 'error', 'related_type', 'related_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }

    public function related()
    {
        return $this->morphTo();
    }
}
