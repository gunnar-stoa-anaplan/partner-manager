<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_session_id',
        'author_user_id',
        'author_contact_id',
        'anonymous',
        'kind',
        'body',
        'status',
        'answered_at',
        'answered_by',
        'hidden_at',
        'hidden_by',
    ];

    protected $casts = [
        'anonymous' => 'boolean',
        'answered_at' => 'datetime',
        'hidden_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkshopSession::class, 'workshop_session_id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function authorContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'author_contact_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(WorkshopReply::class, 'workshop_thread_id')->whereNull('hidden_at');
    }

    public function authorName(): string
    {
        if ($this->anonymous) {
            return 'Anonymous';
        }

        return $this->authorUser?->name ?? $this->authorContact?->full_name ?? 'Unknown';
    }
}
