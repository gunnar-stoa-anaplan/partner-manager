<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortalCan
{
    /**
     * Require the portal user to have at least one of the given capabilities.
     *
     * @param  string  ...$capabilities  Required capabilities (e.g., 'goals', 'projects')
     */
    public function handle(Request $request, Closure $next, string ...$capabilities): Response
    {
        $contact = Auth::guard('portal')->user();

        if (! $contact) {
            abort(403);
        }

        foreach ($capabilities as $capability) {
            if ($contact->canAccess($capability)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this section.');
    }
}
