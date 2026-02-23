<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('action_plans_admin')]
class ActionPlansAdmin
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
