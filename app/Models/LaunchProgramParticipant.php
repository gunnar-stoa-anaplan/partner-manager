<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaunchProgramParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'launch_program_partner_id',
        'contact_id',
        'role',
        'assigned_by',
    ];

    const ROLES = [
        'technical_lead' => 'Technical Lead',
        'sales_lead' => 'Sales Lead',
        'executive_sponsor' => 'Executive Sponsor',
        'participant' => 'Participant',
    ];

    const ROLE_COLORS = [
        'technical_lead' => '#007bff',
        'sales_lead' => '#fd7e14',
        'executive_sponsor' => '#7B1FA2',
        'participant' => '#868e96',
    ];

    public function programPartner(): BelongsTo
    {
        return $this->belongsTo(LaunchProgramPartner::class, 'launch_program_partner_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function milestoneCompletions(): HasMany
    {
        return $this->hasMany(LaunchProgramMilestoneCompletion::class);
    }

    public function completionPercentage(): float
    {
        $requiredMilestones = $this->programPartner->program->milestones()
            ->where('is_required', true)->count();
        if ($requiredMilestones === 0) {
            return 100;
        }

        $completed = $this->milestoneCompletions()
            ->whereHas('milestone', fn ($q) => $q->where('is_required', true))
            ->count();

        return round(($completed / $requiredMilestones) * 100, 1);
    }

    /** @return array<int> */
    public function completedMilestoneIds(): array
    {
        return $this->milestoneCompletions()
            ->pluck('launch_program_milestone_id')
            ->all();
    }
}
