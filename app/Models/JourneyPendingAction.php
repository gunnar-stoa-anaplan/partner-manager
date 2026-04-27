<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyPendingAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'journey_id',
        'journey_participant_id',
        'action',
        'description',
        'assigned_to',
        'status',
        'due_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(JourneyParticipant::class, 'journey_participant_id');
    }
}
