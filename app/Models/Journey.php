<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'journey_key',
        'product_area',
        'persona',
        'status',
        'db_path',
        'config_path',
    ];

    /**
     * Many-to-many relationship with User through journey_assignments.
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'journey_assignments')
            ->withTimestamps();
    }

    /**
     * One-to-many relationship with JourneyAssignment.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(JourneyAssignment::class);
    }

    /**
     * Scope for journeys visible to the given user.
     * Admins see all; PSMs see only assigned journeys.
     */
    public function scopeVisibleTo(\Illuminate\Database\Eloquent\Builder $query, User $user): \Illuminate\Database\Eloquent\Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('assignments', fn ($q) => $q->where('user_id', $user->id));
    }
}
