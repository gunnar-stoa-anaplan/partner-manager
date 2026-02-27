<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('knowledge_base')]
class KnowledgeBase
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
