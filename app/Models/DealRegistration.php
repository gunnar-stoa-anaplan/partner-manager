<?php

namespace App\Models;

use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealRegistration extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'partner_id',
        'contact_id',
        'opportunity_id',
        'deal_name',
        'customer_name',
        'customer_contact_email',
        'estimated_value',
        'expected_close_date',
        'product_area',
        'description',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'expires_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'reviewed_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
    ];

    const STATUS_COLORS = [
        'draft' => 'secondary',
        'submitted' => 'primary',
        'under_review' => 'info',
        'approved' => 'success',
        'rejected' => 'danger',
        'expired' => 'warning',
    ];

    const PRODUCT_AREAS = [
        'Finance' => 'Finance',
        'Supply Chain' => 'Supply Chain',
        'Sales' => 'Sales',
        'Workforce' => 'Workforce',
        'Platform' => 'Platform',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['expired', 'rejected']);
    }

    public function scopeByPartner($query, int $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status !== 'expired';
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (! $this->expires_at || $this->status === 'expired') {
            return null;
        }

        return (int) max(0, now()->diffInDays($this->expires_at, false));
    }
}
