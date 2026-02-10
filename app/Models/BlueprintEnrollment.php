<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlueprintEnrollment extends Model
{
    protected $fillable = [
        'partner_blueprint_assignment_id',
        'contact_id',
        'enrolled_by',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    const STATUSES = [
        'enrolled' => 'Enrolled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];

    const STATUS_COLORS = [
        'enrolled' => '#2196F3',
        'in_progress' => '#fd7e14',
        'completed' => '#28a745',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(PartnerBlueprintAssignment::class, 'partner_blueprint_assignment_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(BlueprintProgress::class);
    }

    // ─── Methods ───────────────────────────────────────────────

    public function completedItemsCount(): int
    {
        return $this->progress()->where('completed', true)->count();
    }

    public function totalApplicableItems(): int
    {
        return $this->assignment->applicableContentItemCount();
    }

    public function completionPercentage(): float
    {
        $total = $this->totalApplicableItems();
        if ($total === 0) return 0;

        return round(($this->completedItemsCount() / $total) * 100, 1);
    }

    public function updateStatus(): void
    {
        $percentage = $this->completionPercentage();

        if ($percentage >= 100) {
            $this->update(['status' => 'completed', 'completed_at' => now()]);
        } elseif ($percentage > 0) {
            $this->update(['status' => 'in_progress', 'completed_at' => null]);
        } else {
            $this->update(['status' => 'enrolled', 'completed_at' => null]);
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#868e96';
    }
}
