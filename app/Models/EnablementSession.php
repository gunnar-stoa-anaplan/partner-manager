<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EnablementSession extends Model
{
    use HasFactory;
    use \App\Models\Concerns\ScopedToActor;

    protected $fillable = [
        'enablement_event_id',
        'title_override',
        'start_at',
        'end_at',
        'all_day',
        'timezone',
        'location',
        'meeting_url',
        'instructor',
        'geography',
        'region',
        'max_participants',
        'status',
        'notes',
        'summary',
        'registration_deadline',
        'created_by',
        'launch_program_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'registration_deadline' => 'datetime',
        'all_day' => 'boolean',
        'max_participants' => 'integer',
    ];

    const STATUSES = [
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    const GEOGRAPHIES = [
        'EMEA' => 'EMEA',
        'NA' => 'North America',
        'APAC' => 'Asia Pacific',
        'LATAM' => 'Latin America',
        'Global' => 'Global',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(EnablementEvent::class, 'enablement_event_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EnablementRegistration::class, 'enablement_session_id');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'enablement_registrations', 'enablement_session_id', 'contact_id')
            ->withPivot('status', 'partner_id', 'nominated_by', 'nominated_at')
            ->withTimestamps();
    }

    public function registeredPartners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_enablement_session')
            ->withPivot('added_by', 'notes')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function launchProgram(): BelongsTo
    {
        return $this->belongsTo(LaunchProgram::class);
    }

    public function labs(): HasMany
    {
        return $this->hasMany(Lab::class, 'enablement_session_id');
    }

    public function zoomMeeting(): HasOne
    {
        return $this->hasOne(ZoomMeeting::class);
    }

    // ─── Accessors ─────────────────────────────────────────────

    public function getDisplayTitleAttribute(): string
    {
        return $this->title_override ?? $this->event->title ?? 'Untitled Session';
    }

    public function getEffectiveMaxParticipantsAttribute(): ?int
    {
        return $this->max_participants ?? $this->event->max_participants;
    }

    public function getIsFullAttribute(): bool
    {
        $max = $this->effective_max_participants;
        if ($max === null) {
            return false;
        }

        return $this->registrations()
            ->whereNotIn('status', ['cancelled'])
            ->count() >= $max;
    }

    public function getIsPastAttribute(): bool
    {
        return $this->start_at->isPast();
    }

    // ─── Methods ───────────────────────────────────────────────

    public function spotsRemaining(): ?int
    {
        $max = $this->effective_max_participants;
        if ($max === null) {
            return null;
        }
        $taken = $this->registrations()
            ->whereNotIn('status', ['cancelled'])
            ->count();

        return max(0, $max - $taken);
    }

    public function isRegistrationOpen(): bool
    {
        if ($this->status === 'cancelled' || $this->status === 'completed') {
            return false;
        }
        if ($this->is_full) {
            return false;
        }
        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return false;
        }
        if ($this->start_at->isPast()) {
            return false;
        }

        return true;
    }

    public function toCalendarArray(): array
    {
        $event = $this->relationLoaded('event') ? $this->event : $this->event()->first();

        return [
            'id' => (string) $this->id,
            'title' => $this->display_title,
            'start' => $this->start_at->toIso8601String(),
            'end' => $this->end_at?->toIso8601String(),
            'allDay' => $this->all_day,
            'backgroundColor' => EnablementEvent::TYPE_COLORS[$event->type ?? 'other'] ?? '#868e96',
            'borderColor' => EnablementEvent::TYPE_COLORS[$event->type ?? 'other'] ?? '#868e96',
            'extendedProps' => [
                'session_id' => $this->id,
                'event_id' => $this->enablement_event_id,
                'event_title' => $event->title ?? '',
                'type' => $event->type ?? 'other',
                'type_label' => EnablementEvent::TYPES[$event->type ?? 'other'] ?? 'Other',
                'category' => $event->category,
                'status' => $this->status,
                'status_label' => self::STATUSES[$this->status] ?? $this->status,
                'location' => $this->location,
                'meeting_url' => $this->meeting_url,
                'instructor' => $this->instructor,
                'geography' => $this->geography,
                'region' => $this->region,
                'description' => $event->description,
                'spots_remaining' => $this->spotsRemaining(),
                'registration_open' => $this->isRegistrationOpen(),
                'registration_count' => $this->relationLoaded('registrations')
                    ? $this->registrations->whereNotIn('status', ['cancelled'])->count()
                    : 0,
            ],
        ];
    }
}
