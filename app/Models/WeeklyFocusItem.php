<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyFocusItem extends Model
{
    /** @use HasFactory<\Database\Factories\WeeklyFocusItemFactory> */
    use HasFactory;

    protected $fillable = [
        'weekly_focus_id',
        'partner_id',
        'description',
        'status',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    const STATUSES = [
        'planned' => 'Planned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'dropped' => 'Dropped',
    ];

    const STATUS_COLORS = [
        'planned' => 'secondary',
        'in_progress' => 'primary',
        'completed' => 'success',
        'dropped' => 'warning',
    ];

    public function weeklyFocus(): BelongsTo
    {
        return $this->belongsTo(WeeklyFocus::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
