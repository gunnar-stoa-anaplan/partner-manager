<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalLoginController extends Controller
{
    public function showLogin(): \Illuminate\View\View
    {
        return view('portal.login');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('portal')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $contact = Auth::guard('portal')->user();

            if (! $contact->portal_access) {
                Auth::guard('portal')->logout();

                return back()->withErrors(['email' => 'Your portal access has been disabled.']);
            }

            $contact->update(['last_login_at' => now()]);

            return redirect()->intended(route('portal.dashboard'));
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
