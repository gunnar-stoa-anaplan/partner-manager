<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('directory_sync')]
class DirectorySync
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
