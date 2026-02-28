<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('measurement')]
class Measurement
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
