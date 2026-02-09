<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
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
     * Check if user is a PSM.
     */
    public function isPsm(): bool
    {
        return $this->role === 'psm';
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        // Generate anonymous avatar with user's initials
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&size=200&background=667eea&color=ffffff&bold=true";
    }
}