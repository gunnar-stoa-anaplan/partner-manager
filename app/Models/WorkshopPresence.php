<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopPresence extends Model
{
    protected $table = 'workshop_presence';

    public $timestamps = false;

    protected $fillable = [
        'workshop_session_id',
        'workshop_page_id',
        'author_user_id',
        'author_contact_id',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkshopSession::class, 'workshop_session_id');
    }

    /**
     * Record (or refresh) presence for whichever guard is authenticated.
     */
    public static function heartbeat(int $sessionId, ?int $pageId, ?int $userId, ?int $contactId): void
    {
        static::updateOrCreate(
            [
                'workshop_session_id' => $sessionId,
                'workshop_page_id' => $pageId,
                'author_user_id' => $userId,
                'author_contact_id' => $contactId,
            ],
            ['last_seen_at' => now()],
        );
    }

    public static function activeCountForSession(int $sessionId, int $withinMinutes = 5): int
    {
        return static::where('workshop_session_id', $sessionId)
            ->where('last_seen_at', '>=', now()->subMinutes($withinMinutes))
            ->get(['author_user_id', 'author_contact_id'])
            ->unique(fn (self $presence) => $presence->author_user_id.'-'.$presence->author_contact_id)
            ->count();
    }
}
