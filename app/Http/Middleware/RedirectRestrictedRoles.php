<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect users with restricted roles to their designated landing page.
 *
 * Applied globally to web routes — checks if the authenticated user has
 * a role that is limited to specific route prefixes. If they try to access
 * anything outside their allowed area, they are redirected.
 */
class RedirectRestrictedRoles
{
    /**
     * Map of restricted roles to their allowed route prefixes and landing route.
     *
     * @var array<string, array{prefixes: array<int, string>, landing: string}>
     */
    private array $restrictions = [
        'integration' => [
            'prefixes' => ['integration-hub', 'imports', 'logout'],
            'landing' => 'integration-hub.dashboard',
        ],
        'coe' => [
            'prefixes' => ['strategy', 'logout', 'users/impersonate', 'welcome', 'livewire', '_boost'],
            'landing' => 'strategy.dashboard',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! isset($this->restrictions[$user->role])) {
            return $next($request);
        }

        $rule = $this->restrictions[$user->role];
        $path = $request->path();

        foreach ($rule['prefixes'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        return redirect()->route($rule['landing']);
    }
}
