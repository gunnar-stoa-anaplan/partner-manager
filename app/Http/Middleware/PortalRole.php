<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortalRole
{
    /**
     * @param  string  ...$roles  Allowed portal roles (e.g., 'admin', 'sales', 'delivery')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $contact = Auth::guard('portal')->user();

        if (! $contact) {
            abort(403, 'You do not have permission to access this section.');
        }

        $slug = $contact->role?->slug;

        if (! in_array($slug, $roles, true)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
