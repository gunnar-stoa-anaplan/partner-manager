<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('zoom_chat')]
class ZoomChat
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
