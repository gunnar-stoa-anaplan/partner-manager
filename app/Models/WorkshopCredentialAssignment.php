<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopCredentialAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_credential_id',
        'workshop_session_id',
        'contact_id',
        'assigned_at',
        'hold_until',
        'released_at',
        'released_reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'hold_until' => 'date',
        'released_at' => 'datetime',
    ];

    const RELEASE_REASONS = [
        'session_ended' => 'Session Ended',
        'removed' => 'Participant Removed',
        'extended' => 'Extended',
        'manual' => 'Manual Release',
    ];

    public function credential(): BelongsTo
    {
        return $this->belongsTo(WorkshopCredential::class, 'workshop_credential_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkshopSession::class, 'workshop_session_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function isActive(): bool
    {
        return $this->released_at === null;
    }
}
