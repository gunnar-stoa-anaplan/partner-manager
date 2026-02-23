<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivationPath extends Model
{
    /** @use HasFactory<\Database\Factories\ActivationPathFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'feature_slug',
        'name',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ActivationStep::class)->orderBy('order');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('assigned_by', 'assigned_at', 'completed_at')
            ->withTimestamps();
    }

    public function isCompletedBy(User $user): bool
    {
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivotNotNull('completed_at')
            ->exists();
    }
}
