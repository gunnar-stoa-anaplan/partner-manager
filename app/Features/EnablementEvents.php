<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('enablement_events')]
class EnablementEvents
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
