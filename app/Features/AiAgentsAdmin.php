<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('ai_agents_admin')]
class AiAgentsAdmin
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
