<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('portal')->check()) {
            return redirect()->route('portal.login');
        }

        $contact = Auth::guard('portal')->user();

        if (! $contact->portal_access) {
            Auth::guard('portal')->logout();

            return redirect()->route('portal.login')
                ->withErrors(['email' => 'Your portal access has been disabled.']);
        }

        return $next($request);
    }
}
