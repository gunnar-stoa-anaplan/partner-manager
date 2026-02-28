<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrIntegration
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->bypassesPermissions() || $user?->hasModulePermission('integration_hub.hub')) {
            return $next($request);
        }

        abort(403, 'Unauthorized. Admin or Integration access required.');
    }
}
