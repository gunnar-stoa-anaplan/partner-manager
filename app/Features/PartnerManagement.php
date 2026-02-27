<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('partner_management')]
class PartnerManagement
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
