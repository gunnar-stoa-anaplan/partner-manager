<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyFocus extends Model
{
    /** @use HasFactory<\Database\Factories\WeeklyFocusFactory> */
    use HasFactory;

    protected $table = 'weekly_focuses';

    protected $fillable = [
        'user_id',
        'week_start',
        'summary',
        'manager_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WeeklyFocusItem::class)->orderBy('sort_order');
    }

    public function completionRate(): float
    {
        $total = $this->items->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->items->where('status', 'completed')->count();

        return round(($completed / $total) * 100, 1);
    }

    public function isReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    public static function currentWeekStart(): Carbon
    {
        return Carbon::now()->startOfWeek(Carbon::MONDAY);
    }

    public function scopeForWeek($query, Carbon $date)
    {
        return $query->where('week_start', $date->startOfWeek(Carbon::MONDAY)->toDateString());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
