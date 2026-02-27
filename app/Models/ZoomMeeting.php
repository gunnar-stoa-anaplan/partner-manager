<?php

namespace App\Models;

use App\Enums\ZoomMeetingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'enablement_session_id',
        'zoom_meeting_id',
        'zoom_uuid',
        'host_email',
        'join_url',
        'start_url',
        'password',
        'topic',
        'agenda',
        'duration_minutes',
        'timezone',
        'status',
        'settings',
        'recording_url',
        'recording_password',
        'started_at',
        'ended_at',
        'participants_count',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ZoomMeetingStatus::class,
            'settings' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_minutes' => 'integer',
            'participants_count' => 'integer',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────

    public function enablementSession(): BelongsTo
    {
        return $this->belongsTo(EnablementSession::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(WorkshopAttendance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopeScheduled($query)
    {
        return $query->where('status', ZoomMeetingStatus::Scheduled);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [ZoomMeetingStatus::Scheduled, ZoomMeetingStatus::Started]);
    }

    // ─── Helpers ───────────────────────────────────────────────

    public function isLive(): bool
    {
        return $this->status === ZoomMeetingStatus::Started;
    }

    public function hasEnded(): bool
    {
        return $this->status === ZoomMeetingStatus::Ended;
    }
}
