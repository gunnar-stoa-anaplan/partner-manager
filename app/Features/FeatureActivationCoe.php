<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

#[Name('feature_activation_coe')]
class FeatureActivationCoe
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): bool
    {
        return false;
    }
}
