<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'slug',
        'name',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    const STATUSES = [
        'upcoming' => 'Upcoming',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(WorkshopSessionParticipant::class);
    }

    public function monitors(): HasMany
    {
        return $this->hasMany(WorkshopSessionMonitor::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'workshop_session_participants')
            ->withPivot('status', 'enrolled_at', 'enrolled_by_user_id', 'enrolled_by_contact_id', 'removed_at')
            ->withTimestamps();
    }

    public function activeParticipants(): HasMany
    {
        return $this->participants()->where('status', '!=', 'removed');
    }
}
