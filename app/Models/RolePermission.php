<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id',
        'module_slug',
        'access_level',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
