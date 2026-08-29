<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopSessionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_session_id',
        'contact_id',
        'status',
        'enrolled_at',
        'enrolled_by_user_id',
        'enrolled_by_contact_id',
        'removed_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    const STATUSES = [
        'invited' => 'Invited',
        'active' => 'Active',
        'removed' => 'Removed',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkshopSession::class, 'workshop_session_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function enrolledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by_user_id');
    }

    public function enrolledByContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'enrolled_by_contact_id');
    }
}
