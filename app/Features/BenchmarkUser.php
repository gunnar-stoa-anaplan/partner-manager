<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('benchmark_user')]
class BenchmarkUser
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
