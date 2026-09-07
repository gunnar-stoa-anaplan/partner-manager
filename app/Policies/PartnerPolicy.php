<?php

namespace App\Policies;

use App\Models\Partner;
use App\Models\User;

/**
 * Reference Policy for the target authorization model: module permission
 * (multi-role union via the User methods) + ownership. Bypass roles short-circuit
 * via before(). Auto-discovered by Laravel for the Partner model.
 */
class PartnerPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->bypassesPermissions() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasModulePermission('partner_management');
    }

    public function view(User $user, Partner $partner): bool
    {
        return $user->hasModulePermission('partner_management') && $this->owns($user, $partner);
    }

    public function create(User $user): bool
    {
        return $user->canWriteModule('partner_management.administration');
    }

    public function update(User $user, Partner $partner): bool
    {
        return $user->canWriteModule('partner_management') && $this->owns($user, $partner);
    }

    public function delete(User $user, Partner $partner): bool
    {
        return $user->canWriteModule('partner_management.administration');
    }

    private function owns(User $user, Partner $partner): bool
    {
        return $user->isAdmin() || $partner->user_id === $user->id;
    }
}
