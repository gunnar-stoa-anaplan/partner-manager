<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrIntegration
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->user()?->role, ['admin', 'integration'])) {
            abort(403, 'Unauthorized. Admin or Integration access required.');
        }

        return $next($request);
    }
}
