<?php

namespace App\Support;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Resolves what the current actor is allowed to SEE, spanning both guards
 * (internal users on `web`, partner contacts on `portal`). This is the single
 * place the internal/external, admin/non-admin, role- and ownership-based
 * scoping is decided; Policies and the ScopedToActor global scope both defer to
 * it. Registered as a singleton so the partner lookups run once per request.
 *
 * Partner scope convention (visiblePartnerIds / visiblePartnerNames):
 *   null  = unrestricted (see everything) — admins, bypass roles, no actor
 *   [...] = restricted to exactly these partners
 *   []    = restricted to nothing
 */
class VisibilityContext
{
    /**
     * The actor identity the current resolution was computed for. Memoization is
     * keyed by this so an early no-actor query (e.g. before auth is available)
     * cannot poison the request — once a real actor appears we re-resolve.
     */
    private ?string $resolvedKey = null;

    /** @var array<int>|null */
    private ?array $partnerIds = null;

    /** @var array<string>|null */
    private ?array $partnerNames = null;

    /**
     * The authenticated actor across every guard that can carry one: internal
     * users on `web`, partner contacts on `portal`, and API/MCP callers on
     * `sanctum` (token auth). Sanctum must be included or a token-authenticated
     * request resolves to no actor and is treated as unrestricted — a data leak
     * on the MCP surface. Resolution is defensive so an unconfigured guard or a
     * console/queue context (no request) simply yields no actor.
     */
    public function actor(): User|Contact|null
    {
        foreach (['web', 'portal', 'sanctum'] as $guard) {
            try {
                $user = Auth::guard($guard)->user();
            } catch (\Throwable) {
                continue; // guard not available in this environment/context
            }

            if ($user instanceof User || $user instanceof Contact) {
                return $user;
            }
        }

        return null;
    }

    public function isInternal(): bool
    {
        return $this->actor() instanceof User;
    }

    public function isPartner(): bool
    {
        return $this->actor() instanceof Contact;
    }

    public function bypasses(): bool
    {
        $a = $this->actor();

        return $a instanceof User && $a->bypassesPermissions();
    }

    /**
     * @return array<int>|null null = unrestricted
     */
    public function visiblePartnerIds(): ?array
    {
        $this->resolve();

        return $this->partnerIds;
    }

    /**
     * @return array<string>|null null = unrestricted
     */
    public function visiblePartnerNames(): ?array
    {
        $this->resolve();

        return $this->partnerNames;
    }

    /**
     * @return array<int> team ids the actor belongs to
     */
    public function teamIds(): array
    {
        $a = $this->actor();

        return $a ? $a->teams()->pluck('teams.id')->all() : [];
    }

    /**
     * The set of internal user ids the actor may act as/for on OWNER-scoped
     * records: the actor plus every recursive reporting-hierarchy descendant
     * (so a manager sees records owned by their reports — DECISION 1). Mirrors
     * the visiblePartnerIds() convention:
     *   null  = unrestricted (no actor / console / bypass roles)
     *   [...] = restricted to exactly these user ids
     *   []    = restricted to nothing (e.g. a portal contact has no internal
     *           user identity, so owner-scoped internal records are not theirs)
     *
     * Increment 3: used by the ScopedToOwner trait for user-owned models
     * (personal records like weekly focus). Program-wide catalog records are not
     * owner-scoped and do not use this.
     *
     * @return array<int>|null
     */
    public function myUserSet(): ?array
    {
        $a = $this->actor();

        if ($a === null) {
            return null; // system / console / queue — unrestricted
        }

        if ($a instanceof User) {
            if ($a->bypassesPermissions()) {
                return null; // super_admin — unrestricted by design
            }

            return array_merge([$a->id], $this->descendantUserIds($a->id));
        }

        return []; // portal contact: no internal user identity
    }

    private function resolve(): void
    {
        $a = $this->actor();
        $key = $a === null ? 'none' : get_class($a).':'.$a->getKey();

        if ($this->resolvedKey === $key) {
            return;
        }
        $this->resolvedKey = $key;

        // Unrestricted (see ALL partners): no actor (console/queue/system),
        // bypass roles (super_admin — by design), and holders of the explicit
        // unrestricted-visibility privilege. Role slugs no longer grant this
        // implicitly — see docs/authorization-spec.md DECISION 5.
        if ($a === null || ($a instanceof User && ($a->bypassesPermissions() || $a->hasUnrestrictedPartnerVisibility()))) {
            $this->partnerIds = null;
            $this->partnerNames = null;

            return;
        }

        if ($a instanceof Contact) {
            $ids = $a->partner_id ? [$a->partner_id] : [];
        } else {
            // Internal non-admin: partners associated with this user AND everyone
            // in their reporting hierarchy — direct ownership (partner.user_id)
            // UNIONed with PSM/PAM assignments (user_partner pivot), for the
            // manager plus all of their (recursive) direct reports.
            $userIds = array_merge([$a->id], $this->descendantUserIds($a->id));

            $owned = Partner::query()->withoutGlobalScopes()
                ->whereIn('user_id', $userIds)->pluck('id');
            $assigned = DB::table('user_partner')->whereIn('user_id', $userIds)->pluck('partner_id');

            $ids = $owned->merge($assigned)->unique()->values()->all();
        }

        $this->partnerIds = $ids;
        $this->partnerNames = $ids
            ? Partner::query()->withoutGlobalScopes()->whereIn('id', $ids)->pluck('name')->all()
            : [];
    }

    /**
     * All user ids in the reporting hierarchy below $rootId (direct reports,
     * their reports, and so on). Excludes the root. Cycle-safe.
     *
     * @return array<int>
     */
    private function descendantUserIds(int $rootId): array
    {
        $all = [];
        $frontier = [$rootId];

        while ($frontier) {
            $children = User::whereIn('manager_id', $frontier)->pluck('id')->all();
            $children = array_values(array_diff($children, $all, [$rootId]));

            if (! $children) {
                break;
            }

            $all = array_merge($all, $children);
            $frontier = $children;
        }

        return $all;
    }
}
