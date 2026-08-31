<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'audience',
        'slug',
        'version',
        'parent_workshop_page_id',
        'superseded_at',
        'published_at',
        'published_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'title',
        'body',
        'sort_order',
        'checks_json',
    ];

    const AUDIENCES = [
        'participant' => 'Participant',
        'facilitator' => 'Facilitator Only',
    ];

    protected $casts = [
        'checks_json' => 'array',
        'superseded_at' => 'datetime',
        'published_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function parentPage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_workshop_page_id');
    }

    public function childVersions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_workshop_page_id');
    }

    public function publishedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function reviewComments(): HasMany
    {
        return $this->hasMany(WorkshopContentReviewComment::class, 'workshop_page_id');
    }

    /**
     * Whether this is the live version sessions attach to, as opposed to a
     * frozen historical version kept only so past check attempts/threads
     * still resolve to the exact content shown at the time.
     */
    public function isCurrent(): bool
    {
        return $this->superseded_at === null;
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * A draft is a proposed revision (or a brand-new page) that hasn't been
     * published live yet and hasn't been rejected either - it's still
     * "open," in PR terms.
     */
    public function isDraft(): bool
    {
        return $this->published_at === null && $this->rejected_at === null;
    }

    public function isRejected(): bool
    {
        return $this->rejected_at !== null;
    }

    public function scopeParticipantFacing(Builder $query): Builder
    {
        return $query->where('audience', 'participant');
    }

    public function scopeFacilitatorOnly(Builder $query): Builder
    {
        return $query->where('audience', 'facilitator');
    }

    public function checkAttempts(): HasMany
    {
        return $this->hasMany(WorkshopCheckAttempt::class);
    }

    public function threads(): HasMany
    {
        return $this->hasMany(WorkshopThread::class);
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    public function checks(): array
    {
        return $this->checks_json ?? [];
    }

    public function completionFor(int $contactId, int $sessionId): array
    {
        $checks = $this->checks();

        if (empty($checks)) {
            return ['total' => 0, 'passed' => 0, 'complete' => true];
        }

        $passedIds = $this->checkAttempts()
            ->where('contact_id', $contactId)
            ->where('workshop_session_id', $sessionId)
            ->where('passed', true)
            ->pluck('check_id')
            ->unique();

        $passedCount = collect($checks)->filter(fn (array $check) => $passedIds->contains($check['id']))->count();

        return [
            'total' => count($checks),
            'passed' => $passedCount,
            'complete' => $passedCount === count($checks),
        ];
    }
}
