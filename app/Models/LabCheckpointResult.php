<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabCheckpointResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_participant_id',
        'lab_checkpoint_id',
        'passed',
        'validated_at',
        'validation_details',
        'error_message',
        'attempt_number',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'validated_at' => 'datetime',
            'validation_details' => 'array',
            'attempt_number' => 'integer',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(LabParticipant::class, 'lab_participant_id');
    }

    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(LabCheckpoint::class, 'lab_checkpoint_id');
    }
}
