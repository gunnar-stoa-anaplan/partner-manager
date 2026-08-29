<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'slug',
        'title',
        'body',
        'sort_order',
        'checks_json',
    ];

    protected $casts = [
        'checks_json' => 'array',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
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
