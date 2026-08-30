<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'slug',
        'name',
        'start_date',
        'end_date',
        'status',
        'archived_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'archived_at' => 'datetime',
    ];

    const STATUSES = [
        'upcoming' => 'Upcoming',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'archived' => 'Archived',
    ];

    /**
     * Sessions stay open for discussion this many days past their end date
     * before automatically locking, so recent cohorts can keep posting
     * follow-up questions without a facilitator having to take any action.
     */
    const LOCK_GRACE_DAYS = 14;

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(WorkshopSessionParticipant::class);
    }

    public function monitors(): HasMany
    {
        return $this->hasMany(WorkshopSessionMonitor::class);
    }

    public function credentialAssignments(): HasMany
    {
        return $this->hasMany(WorkshopCredentialAssignment::class);
    }

    public function threads(): HasMany
    {
        return $this->hasMany(WorkshopThread::class);
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(WorkshopSurveyResponse::class, 'workshop_session_id');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'workshop_session_participants')
            ->withPivot('status', 'enrolled_at', 'enrolled_by_user_id', 'enrolled_by_contact_id', 'removed_at')
            ->withTimestamps();
    }

    public function activeParticipants(): HasMany
    {
        return $this->participants()->where('status', '!=', 'removed');
    }

    /**
     * A session is locked (read-only) once it's been archived, or once its
     * end date plus the grace window has passed - lets recent cohorts keep
     * posting follow-up questions without a facilitator taking any action,
     * while stale cohorts retire automatically with no cron required.
     */
    public function isLocked(): bool
    {
        if ($this->status === 'archived') {
            return true;
        }

        if (! $this->end_date) {
            return false;
        }

        return $this->end_date->addDays(self::LOCK_GRACE_DAYS)->isPast();
    }
}
