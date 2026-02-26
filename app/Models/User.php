<?php

namespace App\Models;

use App\Models\IdeaRoom\Bucket;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Room;
use App\Models\IdeaRoom\Snapshot;
use App\Models\IdeaRoom\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'avatar',
        'preferences',
        'timezone',
    ];

    /**
     * Attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributes casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    /**
     * One-to-one relationship with SalesforceCredential.
     */
    public function salesforceCredential()
    {
        return $this->hasOne(SalesforceCredential::class);
    }

    /**
     * One-to-many relationship with Partner (owned partners).
     */
    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    /**
     * Many-to-many relationship with Partner through user_partner pivot (PSM assignments).
     */
    public function assignedPartners()
    {
        return $this->belongsToMany(Partner::class, 'user_partner')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get partners where this user is assigned as PSM.
     */
    public function psmPartners()
    {
        return $this->belongsToMany(Partner::class, 'user_partner')
            ->wherePivot('role', 'psm')
            ->withTimestamps();
    }

    /**
     * The user's direct manager.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Users who report directly to this user.
     */
    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Check if user has admin-level access (admin or super_admin).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Check if user can manage the reporting hierarchy.
     */
    public function canManageHierarchy(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'coe', 'manager']);
    }

    /**
     * Check if user is a PSM.
     */
    public function isPsm(): bool
    {
        return $this->role === 'psm';
    }

    // ── Activation Path relationships ────────────────────────────

    public function activationPaths(): BelongsToMany
    {
        return $this->belongsToMany(ActivationPath::class)
            ->withPivot('assigned_by', 'assigned_at', 'completed_at')
            ->withTimestamps();
    }

    public function hasCompletedActivationFor(string $featureSlug): bool
    {
        return $this->activationPaths()
            ->where('feature_slug', $featureSlug)
            ->wherePivotNotNull('completed_at')
            ->exists();
    }

    // ── Idea Room relationships ──────────────────────────────────

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }

    public function ideaBuckets(): HasMany
    {
        return $this->hasMany(Bucket::class);
    }

    public function ideaTags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function ideaRooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function ideaSnapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class, 'created_by');
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/'.$this->avatar);
        }

        // Generate anonymous avatar with user's initials
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&size=200&background=667eea&color=ffffff&bold=true";
    }
}
