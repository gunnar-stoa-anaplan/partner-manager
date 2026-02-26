<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'display_name',
        'type',
        'is_protected',
        'bypass_permissions',
        'landing_route',
        'dashboard_group',
        'sort_order',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
            'bypass_permissions' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'portal_role_id');
    }

    /**
     * Check if this role has permission for a given module slug.
     */
    public function hasPermission(string $moduleSlug): bool
    {
        if ($this->bypass_permissions) {
            return true;
        }

        return $this->permissions->contains('module_slug', $moduleSlug);
    }

    /**
     * Check if this role has write access for a given module slug.
     */
    public function hasWritePermission(string $moduleSlug): bool
    {
        if ($this->bypass_permissions) {
            return true;
        }

        return $this->permissions
            ->where('module_slug', $moduleSlug)
            ->where('access_level', 'write')
            ->isNotEmpty();
    }

    /**
     * Get the access level for a given module slug (null if no access).
     */
    public function accessLevel(string $moduleSlug): ?string
    {
        if ($this->bypass_permissions) {
            return 'write';
        }

        return $this->permissions
            ->where('module_slug', $moduleSlug)
            ->first()
            ?->access_level;
    }

    public function scopeInternal($query)
    {
        return $query->where('type', 'internal');
    }

    public function scopePortal($query)
    {
        return $query->where('type', 'portal');
    }
}
