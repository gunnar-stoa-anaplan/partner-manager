<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaunchProgramMilestoneCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'launch_program_participant_id',
        'launch_program_milestone_id',
        'completed_at',
        'evidence_url',
        'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(LaunchProgramParticipant::class, 'launch_program_participant_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(LaunchProgramMilestone::class, 'launch_program_milestone_id');
    }
}
