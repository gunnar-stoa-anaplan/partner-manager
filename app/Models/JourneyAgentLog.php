<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyAgentLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'journey_id',
        'journey_participant_id',
        'level',
        'event',
        'message',
        'context',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
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
