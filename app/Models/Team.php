<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * A team / function (Sales, Marketing, Service, Implementation, …). Members are
 * polymorphic so both internal users and partner contacts can belong — the axis
 * for cross-boundary collaboration and visibility.
 */
class Team extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'member', 'team_members')
            ->withPivot('role_in_team')->withTimestamps();
    }

    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(Contact::class, 'member', 'team_members')
            ->withPivot('role_in_team')->withTimestamps();
    }
}
