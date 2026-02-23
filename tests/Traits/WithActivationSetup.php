<?php

namespace Tests\Traits;

use App\Models\ActivationPath;
use App\Models\User;
use Laravel\Pennant\Feature;

trait WithActivationSetup
{
    /**
     * Activate both layers (Pennant instance flag + user's path completion) for a module.
     */
    protected function activateModuleForUser(string $slug, User $user): void
    {
        Feature::for(null)->activate($slug);

        $path = ActivationPath::firstOrCreate(
            ['feature_slug' => $slug],
            ['name' => $slug, 'is_active' => true],
        );

        $path->users()->syncWithoutDetaching([
            $user->id => ['assigned_by' => $user->id, 'completed_at' => now()],
        ]);
    }
}
