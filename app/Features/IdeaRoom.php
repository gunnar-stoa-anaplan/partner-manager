<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('idea_room')]
class IdeaRoom
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
