<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaunchProgramPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'launch_program_id',
        'partner_id',
        'status',
        'nominated_by',
        'accepted_at',
        'notes',
    ];

    const STATUSES = [
        'nominated' => 'Nominated',
        'accepted' => 'Accepted',
        'active' => 'Active',
        'withdrawn' => 'Withdrawn',
        'completed' => 'Completed',
    ];

    const STATUS_COLORS = [
        'nominated' => '#fd7e14',
        'accepted' => '#007bff',
        'active' => '#28a745',
        'withdrawn' => '#868e96',
        'completed' => '#17a2b8',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LaunchProgram::class, 'launch_program_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function nominator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nominated_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(LaunchProgramParticipant::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(LaunchProgramFeedback::class);
    }

    public function completionPercentage(): float
    {
        $requiredMilestones = $this->program->milestones()->where('is_required', true)->count();
        if ($requiredMilestones === 0) {
            return 100;
        }

        $participants = $this->participants()->with('milestoneCompletions')->get();
        if ($participants->isEmpty()) {
            return 0;
        }

        $total = 0;
        foreach ($participants as $participant) {
            $completed = $participant->milestoneCompletions()
                ->whereHas('milestone', fn ($q) => $q->where('is_required', true))
                ->count();
            $total += ($completed / $requiredMilestones) * 100;
        }

        return round($total / $participants->count(), 1);
    }
}
