<?php

namespace App\Support;

use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    public function actor(): User|Contact|null
    {
        return Auth::guard('web')->user() ?? Auth::guard('portal')->user();
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

    private function resolve(): void
    {
        $a = $this->actor();
        $key = $a === null ? 'none' : get_class($a).':'.$a->getKey();

        if ($this->resolvedKey === $key) {
            return;
        }
        $this->resolvedKey = $key;

        // No actor (console, queue, system) or unrestricted internal → see all.
        if ($a === null || ($a instanceof User && ($a->bypassesPermissions() || $a->isAdmin()))) {
            $this->partnerIds = null;
            $this->partnerNames = null;

            return;
        }

        if ($a instanceof Contact) {
            $ids = $a->partner_id ? [$a->partner_id] : [];
        } else {
            // Internal non-admin: the partners they are associated with —
            // direct ownership (partner.user_id) UNIONed with the canonical
            // PSM/PAM assignments in the user_partner pivot (assignedPartners()).
            // withoutGlobalScopes avoids recursing into this same scope.
            $owned = Partner::query()->withoutGlobalScopes()
                ->where('user_id', $a->id)->pluck('id');
            $assigned = $a->assignedPartners()->withoutGlobalScopes()->pluck('partners.id');

            $ids = $owned->merge($assigned)->unique()->values()->all();
        }

        $this->partnerIds = $ids;
        $this->partnerNames = $ids
            ? Partner::query()->withoutGlobalScopes()->whereIn('id', $ids)->pluck('name')->all()
            : [];
    }
}
