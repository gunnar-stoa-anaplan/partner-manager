<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Concerns\ScopedToActor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnablementRegistration extends Model
{
    use HasFactory, ScopedToActor;

    protected $fillable = [
        'enablement_session_id',
        'contact_id',
        'partner_id',
        'nominated_by',
        'status',
        'nominated_at',
        'registered_at',
        'confirmed_at',
        'attended_at',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'nominated_at' => 'datetime',
        'registered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    const STATUSES = [
        'nominated' => ['label' => 'Nominated', 'color' => 'info'],
        'registered' => ['label' => 'Registered', 'color' => 'primary'],
        'confirmed' => ['label' => 'Confirmed', 'color' => 'success'],
        'attended' => ['label' => 'Attended', 'color' => 'success'],
        'no_show' => ['label' => 'No Show', 'color' => 'warning'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger'],
    ];

    // ─── Relationships ─────────────────────────────────────────

    public function session(): BelongsTo
    {
        return $this->belongsTo(EnablementSession::class, 'enablement_session_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function nominator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nominated_by');
    }

    // ─── Status Methods ────────────────────────────────────────

    public function markRegistered(): self
    {
        $this->update([
            'status' => 'registered',
            'registered_at' => Carbon::now(),
        ]);
        return $this;
    }

    public function markConfirmed(): self
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => Carbon::now(),
        ]);
        return $this;
    }

    public function markAttended(): self
    {
        $this->update([
            'status' => 'attended',
            'attended_at' => Carbon::now(),
        ]);
        return $this;
    }

    public function markNoShow(): self
    {
        $this->update([
            'status' => 'no_show',
        ]);
        return $this;
    }

    public function cancel(?string $reason = null): self
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);
        return $this;
    }

    // ─── Helpers ───────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    // ─── Boot ──────────────────────────────────────────────────

    protected static function booted()
    {
        static::creating(function ($registration) {
            if (empty($registration->nominated_at)) {
                $registration->nominated_at = Carbon::now();
            }
        });
    }
}
