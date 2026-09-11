<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerBlueprintAssignment extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'partner_id',
        'blueprint_id',
        'role',
        'assigned_by',
        'status',
        'notes',
    ];

    const ROLES = [
        'gtm' => 'GTM (Sales)',
        'pre_sales' => 'Pre-Sales',
        'delivery' => 'Delivery',
    ];

    const STATUSES = [
        'active' => 'Active',
        'completed' => 'Completed',
        'paused' => 'Paused',
    ];

    const ROLE_COLORS = [
        'gtm' => '#fd7e14',
        'pre_sales' => '#886ab5',
        'delivery' => '#00BCD4',
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(BlueprintEnrollment::class, 'partner_blueprint_assignment_id');
    }

    // ─── Methods ───────────────────────────────────────────────

    public function applicableContentItems()
    {
        $roleColumn = match ($this->role) {
            'gtm' => 'gtm_applicable',
            'pre_sales' => 'pre_sales_applicable',
            'delivery' => 'delivery_applicable',
        };

        return BlueprintContentItem::whereHas('outcome', function ($q) {
            $q->where('blueprint_id', $this->blueprint_id);
        })->where($roleColumn, '!=', 'na')->get();
    }

    public function applicableContentItemCount(): int
    {
        // The count depends only on (blueprint, role), not the assignment — so
        // memoize per request to avoid a query per enrollment on dashboards.
        static $cache = [];
        $key = $this->blueprint_id.':'.$this->role;

        return $cache[$key] ??= $this->applicableContentItems()->count();
    }

    public function completionPercentage(): float
    {
        $totalItems = $this->applicableContentItemCount();
        if ($totalItems === 0) {
            return 0;
        }

        $completedCount = 0;
        foreach ($this->enrollments as $enrollment) {
            $completedCount += $enrollment->completedItemsCount();
        }

        $enrollmentCount = $this->enrollments->count();
        if ($enrollmentCount === 0) {
            return 0;
        }

        return round(($completedCount / ($totalItems * $enrollmentCount)) * 100, 1);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function getRoleColorAttribute(): string
    {
        return self::ROLE_COLORS[$this->role] ?? '#868e96';
    }
}
