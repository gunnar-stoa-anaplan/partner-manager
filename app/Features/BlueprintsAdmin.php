<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('blueprints_admin')]
class BlueprintsAdmin
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
