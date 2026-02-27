<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('enablement')]
class Enablement
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
