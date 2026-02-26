<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'zoom_meeting_id',
        'enablement_registration_id',
        'participant_email',
        'participant_name',
        'zoom_user_id',
        'join_time',
        'leave_time',
        'duration_seconds',
        'attentiveness_score',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'join_time' => 'datetime',
            'leave_time' => 'datetime',
            'duration_seconds' => 'integer',
            'attentiveness_score' => 'integer',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────

    public function zoomMeeting(): BelongsTo
    {
        return $this->belongsTo(ZoomMeeting::class);
    }

    public function enablementRegistration(): BelongsTo
    {
        return $this->belongsTo(EnablementRegistration::class);
    }
}
