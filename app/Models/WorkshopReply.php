<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_thread_id',
        'author_user_id',
        'author_contact_id',
        'is_monitor_reply',
        'body',
        'hidden_at',
        'hidden_by',
    ];

    protected $casts = [
        'is_monitor_reply' => 'boolean',
        'hidden_at' => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(WorkshopThread::class, 'workshop_thread_id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function authorContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'author_contact_id');
    }

    public function authorName(): string
    {
        return $this->authorUser?->name ?? $this->authorContact?->full_name ?? 'Unknown';
    }

    public function uploads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkshopUpload::class, 'workshop_reply_id');
    }
}
