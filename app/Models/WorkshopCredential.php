<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopCredential extends Model
{
    use HasFactory;

    protected $table = 'workshop_credential_pool';

    protected $fillable = [
        'workshop_id',
        'email',
        'username',
        'password_enc',
        'status',
        'last_refreshed_at',
        'notes',
    ];

    protected $casts = [
        'password_enc' => 'encrypted',
        'last_refreshed_at' => 'datetime',
    ];

    const STATUSES = [
        'available' => 'Available',
        'maintenance' => 'Maintenance',
        'retired' => 'Retired',
    ];

    /**
     * Fields the audit trail (WorkshopContentAuditObserver) must never
     * record, even encrypted - the sandbox password.
     */
    const AUDIT_EXCLUDED = ['password_enc'];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkshopCredentialAssignment::class, 'workshop_credential_id');
    }

    public function activeAssignment(): ?WorkshopCredentialAssignment
    {
        return $this->assignments()->whereNull('released_at')->first();
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && ! $this->activeAssignment();
    }
}
