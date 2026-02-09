<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'created_by',
        'title',
        'type',
        'status',
        'start_at',
        'end_at',
        'all_day',
        'location',
        'description',
        'outcome',
        'next_steps',
        'opportunity_id',
        'project_id',
        'recurrence_type',
        'recurrence_end',
        'parent_event_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'recurrence_end' => 'date',
        'all_day' => 'boolean',
    ];

    const TYPES = [
        'qbr' => 'QBR',
        'training' => 'Training',
        'workshop' => 'Workshop',
        'demo' => 'Demo',
        'joint_sales_call' => 'Joint Sales Call',
        'check_in' => 'Check-in',
        'escalation_review' => 'Escalation Review',
        'certification_exam' => 'Certification Exam',
        'kickoff' => 'Kickoff',
        'other' => 'Other',
    ];

    const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    const TYPE_COLORS = [
        'qbr' => '#886ab5',
        'training' => '#2196F3',
        'workshop' => '#7B1FA2',
        'demo' => '#28a745',
        'joint_sales_call' => '#1B5E20',
        'check_in' => '#92afd7',
        'escalation_review' => '#dc3545',
        'certification_exam' => '#fd7e14',
        'kickoff' => '#20c997',
        'other' => '#868e96',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_partner_event')
            ->withTimestamps();
    }

    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(PartnerEvent::class, 'parent_event_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(PartnerEvent::class, 'parent_event_id');
    }

    // ─── Recurrence Expansion ──────────────────────────────────

    /**
     * Expand recurring events into virtual occurrences within a date range.
     */
    public function expandOccurrences(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        if ($this->recurrence_type === 'none') {
            return [$this->toCalendarArray()];
        }

        $occurrences = [];
        $duration = $this->end_at ? $this->start_at->diffInMinutes($this->end_at) : 0;
        $recurrenceEnd = $this->recurrence_end
            ? Carbon::parse($this->recurrence_end)->endOfDay()
            : $rangeEnd;
        $effectiveEnd = $recurrenceEnd->lt($rangeEnd) ? $recurrenceEnd : $rangeEnd;

        $interval = match ($this->recurrence_type) {
            'daily' => '1 day',
            'weekly' => '1 week',
            'biweekly' => '2 weeks',
            'monthly' => '1 month',
            default => null,
        };

        if (!$interval) {
            return [$this->toCalendarArray()];
        }

        // Get exception dates to skip
        $exceptionDates = $this->exceptions()
            ->pluck('start_at')
            ->map(fn($d) => $d->toDateString())
            ->toArray();

        $current = $this->start_at->copy();
        while ($current->lte($effectiveEnd)) {
            if ($current->gte($rangeStart) && !in_array($current->toDateString(), $exceptionDates)) {
                $occurrence = $this->toCalendarArray();
                $occurrence['start'] = $current->toIso8601String();
                if ($this->end_at) {
                    $occurrence['end'] = $current->copy()->addMinutes($duration)->toIso8601String();
                }
                $occurrence['id'] = $this->id . '_' . $current->format('Ymd');
                $occurrence['extendedProps']['parent_event_id'] = $this->id;
                $occurrence['extendedProps']['is_occurrence'] = true;
                $occurrences[] = $occurrence;
            }
            $current = $current->add($interval);
        }

        return $occurrences;
    }

    /**
     * Serialize to FullCalendar-compatible JSON.
     */
    public function toCalendarArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'start' => $this->start_at->toIso8601String(),
            'end' => $this->end_at?->toIso8601String(),
            'allDay' => $this->all_day,
            'backgroundColor' => self::TYPE_COLORS[$this->type] ?? '#868e96',
            'borderColor' => self::TYPE_COLORS[$this->type] ?? '#868e96',
            'extendedProps' => [
                'event_id' => $this->id,
                'partner_id' => $this->partner_id,
                'partner_name' => $this->partner?->name,
                'type' => $this->type,
                'type_label' => self::TYPES[$this->type] ?? $this->type,
                'status' => $this->status,
                'status_label' => self::STATUSES[$this->status] ?? $this->status,
                'location' => $this->location,
                'description' => $this->description,
                'outcome' => $this->outcome,
                'next_steps' => $this->next_steps,
                'opportunity_id' => $this->opportunity_id,
                'project_id' => $this->project_id,
                'recurrence_type' => $this->recurrence_type,
                'recurrence_end' => $this->recurrence_end?->toDateString(),
                'parent_event_id' => $this->parent_event_id,
                'is_occurrence' => false,
                'participants' => $this->relationLoaded('participants')
                    ? $this->participants->map(fn($c) => [
                        'id' => $c->id,
                        'name' => $c->first_name . ' ' . $c->last_name,
                    ])->toArray()
                    : [],
            ],
        ];
    }
}
