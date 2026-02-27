<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('directory_sync_admin')]
class DirectorySyncAdmin
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
