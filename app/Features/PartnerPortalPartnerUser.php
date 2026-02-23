<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('partner_portal_partner_user')]
class PartnerPortalPartnerUser
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
