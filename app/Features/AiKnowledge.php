<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('ai_knowledge')]
class AiKnowledge
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
