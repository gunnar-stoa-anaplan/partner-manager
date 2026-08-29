<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopReadState extends Model
{
    protected $table = 'workshop_read_state';

    public $timestamps = false;

    protected $fillable = [
        'workshop_thread_id',
        'author_user_id',
        'author_contact_id',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(WorkshopThread::class, 'workshop_thread_id');
    }

    public static function markRead(int $threadId, ?int $userId, ?int $contactId): void
    {
        static::updateOrCreate(
            ['workshop_thread_id' => $threadId, 'author_user_id' => $userId, 'author_contact_id' => $contactId],
            ['last_read_at' => now()],
        );
    }
}
