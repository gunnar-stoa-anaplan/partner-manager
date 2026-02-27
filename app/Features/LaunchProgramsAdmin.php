<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('launch_programs_admin')]
class LaunchProgramsAdmin
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
