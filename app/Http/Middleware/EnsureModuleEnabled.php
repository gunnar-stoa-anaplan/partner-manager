<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        private ModuleRegistry $registry,
    ) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! $this->registry->isEnabled($module, $request->user())) {
            // Allow access if the user has an assigned activation path for this module
            // (training in progress — they need access to complete Tell/Show/Do steps)
            if ($this->userHasAssignedPath($request->user(), $module)) {
                return $next($request);
            }

            abort(403, 'This module is not enabled for your instance.');
        }

        return $next($request);
    }

    /**
     * Check if the user has an assigned (but not necessarily completed) activation path for this module.
     */
    private function userHasAssignedPath(?User $user, string $module): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->activationPaths()
            ->where('feature_slug', $module)
            ->exists();
    }
}
