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

    /**
     * @param  string  $identifier  Feature slug ("enablement") or feature.section ("enablement.calendar")
     */
    public function handle(Request $request, Closure $next, string $identifier): Response
    {
        $user = $request->user();

        if (! $this->registry->isAccessible($identifier, $user instanceof User ? $user : null)) {
            abort(403, 'This module is not enabled for your instance.');
        }

        // Enforce write permission for mutating requests (internal users with role_id only)
        if ($user instanceof User && $user->role_id !== null && ! $request->isMethodSafe() && ! $user->canWriteModule($identifier)) {
            abort(403, 'You do not have write access to this module.');
        }

        return $next($request);
    }
}
