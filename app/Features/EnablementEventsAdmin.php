<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('enablement_events_admin')]
class EnablementEventsAdmin
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
