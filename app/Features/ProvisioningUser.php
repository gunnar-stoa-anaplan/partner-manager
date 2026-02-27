<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('provisioning_user')]
class ProvisioningUser
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
