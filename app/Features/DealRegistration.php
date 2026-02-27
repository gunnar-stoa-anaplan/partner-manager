<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('deal_registration')]
class DealRegistration
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
