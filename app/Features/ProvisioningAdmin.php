<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('provisioning_admin')]
class ProvisioningAdmin
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
