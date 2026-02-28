<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * Allows users who bypass permissions (admin, super_admin) and optionally
     * additional roles passed as middleware parameters: admin:coe
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$extraRoles): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        if ($user->bypassesPermissions()) {
            return $next($request);
        }

        if ($extraRoles && in_array($user->role?->slug, $extraRoles, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. Admin access required.');
    }
}
