<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'contact_id',
        'partner_id',
        'assigned_model_id',
        'status',
        'started_at',
        'completed_at',
        'total_points_earned',
        'total_points_possible',
        'enrolled_by',
    ];

    const STATUSES = [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];

    const STATUS_COLORS = [
        'not_started' => '#868e96',
        'in_progress' => '#fd7e14',
        'completed' => '#28a745',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_points_earned' => 'integer',
            'total_points_possible' => 'integer',
        ];
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function assignedModel(): BelongsTo
    {
        return $this->belongsTo(AnaplanModel::class, 'assigned_model_id');
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function checkpointResults(): HasMany
    {
        return $this->hasMany(LabCheckpointResult::class);
    }

    public function completionPercentage(): float
    {
        if ($this->total_points_possible === 0) {
            return 0;
        }

        return round(($this->total_points_earned / $this->total_points_possible) * 100, 1);
    }

    public function checkpointsPassed(): int
    {
        return $this->checkpointResults()
            ->where('passed', true)
            ->distinct('lab_checkpoint_id')
            ->count('lab_checkpoint_id');
    }

    public function updateProgress(): void
    {
        $totalPoints = $this->lab->totalPoints();

        $passedCheckpointIds = $this->checkpointResults()
            ->where('passed', true)
            ->pluck('lab_checkpoint_id')
            ->unique();

        $pointsEarned = LabCheckpoint::whereIn('id', $passedCheckpointIds)->sum('points');

        $updates = [
            'total_points_earned' => $pointsEarned,
            'total_points_possible' => $totalPoints,
        ];

        if ($pointsEarned >= $totalPoints && $totalPoints > 0) {
            $updates['status'] = 'completed';
            $updates['completed_at'] = now();
        } elseif ($pointsEarned > 0 && $this->status === 'not_started') {
            $updates['status'] = 'in_progress';
            $updates['started_at'] = $this->started_at ?? now();
        }

        $this->update($updates);
    }

    /**
     * Get the latest result for a specific checkpoint.
     */
    public function latestResultFor(LabCheckpoint $checkpoint): ?LabCheckpointResult
    {
        return $this->checkpointResults()
            ->where('lab_checkpoint_id', $checkpoint->id)
            ->latest('attempt_number')
            ->first();
    }
}
