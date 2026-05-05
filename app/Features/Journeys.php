<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('journeys')]
class Journeys
{
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
