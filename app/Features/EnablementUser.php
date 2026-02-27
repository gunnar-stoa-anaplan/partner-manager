<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('enablement_user')]
class EnablementUser
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
