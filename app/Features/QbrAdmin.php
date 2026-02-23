<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('qbr_admin')]
class QbrAdmin
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
