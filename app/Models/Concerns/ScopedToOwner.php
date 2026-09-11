<?php

namespace App\Models\Concerns;

use App\Support\VisibilityContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Fail-closed row-level visibility for USER-OWNED records that have no partner
 * anchor (the second principal axis — increment 3). A model using this trait is
 * constrained to records owned by (or assigned to) the actor's user set: the
 * actor plus their recursive reporting-hierarchy descendants
 * (VisibilityContext::myUserSet). Unrestricted actors (bypass roles,
 * console/queue with no actor) are never filtered.
 *
 * Distinct from ScopedToActor (partner axis). A model that is partner-anchored
 * should use ScopedToActor; a purely user-owned record uses this. Do NOT put
 * this on Authenticatable models (it would recurse through the auth guard) —
 * those use a local query scope instead (see Contact::scopeVisibleToActor).
 *
 * Owner columns default to ['user_id']; override ownerColumns() for models whose
 * principal is created_by / owner_id / assigned_to (visibility is the OR of them).
 */
trait ScopedToOwner
{
    protected static function bootScopedToOwner(): void
    {
        static::addGlobalScope('owner_visibility', function (Builder $builder) {
            $ids = app(VisibilityContext::class)->myUserSet();

            if ($ids === null) {
                return; // unrestricted
            }

            $model = new static;
            $table = $model->getTable();
            $columns = $model->ownerColumns();
            $ids = $ids ?: [0]; // empty set → match nothing (fail-closed)

            $builder->where(function (Builder $q) use ($columns, $table, $ids) {
                foreach ($columns as $column) {
                    $q->orWhereIn($table.'.'.$column, $ids);
                }
            });
        });
    }

    /**
     * The columns that hold an owning/assigned user id. Visibility is the OR of
     * these against the actor's user set.
     *
     * @return array<int, string>
     */
    public function ownerColumns(): array
    {
        return ['user_id'];
    }
}
