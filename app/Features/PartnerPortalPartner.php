<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('partner_portal_partner')]
class PartnerPortalPartner
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
