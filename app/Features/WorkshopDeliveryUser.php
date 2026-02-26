<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('workshop_delivery_user')]
class WorkshopDeliveryUser
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
