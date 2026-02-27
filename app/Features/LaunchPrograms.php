<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('launch_programs')]
class LaunchPrograms
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
