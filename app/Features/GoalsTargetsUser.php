<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('goals_targets_user')]
class GoalsTargetsUser
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
