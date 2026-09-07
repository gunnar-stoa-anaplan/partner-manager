<?php

namespace App\Models\Concerns;

use App\Support\VisibilityContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Fail-closed row-level visibility. Any model using this trait is automatically
 * constrained to the partners the current actor may see (VisibilityContext).
 * Unrestricted actors (admins, bypass roles, console/queue with no actor) are
 * never filtered. Opt out explicitly with ->withoutGlobalScope('actor_visibility').
 *
 * Models whose partner key is not `id` override visibilityPartnerColumn().
 */
trait ScopedToActor
{
    protected static function bootScopedToActor(): void
    {
        static::addGlobalScope('actor_visibility', function (Builder $builder) {
            $ids = app(VisibilityContext::class)->visiblePartnerIds();

            if ($ids === null) {
                return; // unrestricted
            }

            $column = (new static)->visibilityPartnerColumn();
            $builder->whereIn($builder->getModel()->getTable().'.'.$column, $ids ?: [0]);
        });
    }

    /**
     * The column that holds the partner id for visibility scoping. Defaults to
     * `partner_id` (the common case for partner-owned models); the Partner model
     * itself overrides this to `id`.
     */
    public function visibilityPartnerColumn(): string
    {
        return 'partner_id';
    }
}
