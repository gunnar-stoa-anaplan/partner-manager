<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JourneyParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'journey_id',
        'name',
        'email',
        'external_id',
        'current_stage',
        'enrolled_at',
        'last_active_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'last_active_at' => 'datetime',
            'metadata' => 'array',
            'current_stage' => 'integer',
        ];
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(JourneyStageHistory::class);
    }

    public function pendingActions(): HasMany
    {
        return $this->hasMany(JourneyPendingAction::class);
    }
}
