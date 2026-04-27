<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyStageHistory extends Model
{
    use HasFactory;

    protected $table = 'journey_stage_history';

    protected $fillable = [
        'journey_participant_id',
        'from_stage',
        'to_stage',
        'changed_by',
        'note',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'from_stage' => 'integer',
            'to_stage' => 'integer',
            'changed_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(JourneyParticipant::class, 'journey_participant_id');
    }
}
