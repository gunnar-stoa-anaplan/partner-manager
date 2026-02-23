<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('qbr_user')]
class QbrUser
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
