<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('project_health_user')]
class ProjectHealthUser
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
