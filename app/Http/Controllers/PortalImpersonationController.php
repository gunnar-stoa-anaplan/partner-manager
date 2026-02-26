<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalImpersonationController extends Controller
{
    /**
     * Start impersonating a portal user (Contact).
     */
    public function start(Request $request, Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $partner = $contact->partner;
        $this->authorizeImpersonation($partner);

        if (! $contact->portal_access || ! $contact->portal_role) {
            return back()->with('error', 'This contact does not have active portal access.');
        }

        $request->session()->put('impersonating_portal', true);
        $request->session()->put('impersonator_user_id', auth()->id());
        $request->session()->put('impersonator_user_name', auth()->user()->name);
        $request->session()->put('impersonation_partner_id', $partner->id);

        Auth::guard('portal')->login($contact);

        return redirect()->route('portal.dashboard');
    }

    /**
     * Stop impersonating and return to the admin interface.
     */
    public function stop(Request $request): \Illuminate\Http\RedirectResponse
    {
        $partnerId = $request->session()->get('impersonation_partner_id');

        Auth::guard('portal')->logout();

        $request->session()->forget([
            'impersonating_portal',
            'impersonator_user_id',
            'impersonator_user_name',
            'impersonation_partner_id',
        ]);

        if ($partnerId) {
            return redirect()->route('partners.show', $partnerId)
                ->with('success', 'Returned from portal impersonation.');
        }

        return redirect()->route('welcome');
    }

    private function authorizeImpersonation($partner): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($partner->user_id === $user->id) {
            return;
        }

        abort(403, 'You are not authorized to impersonate users for this partner.');
    }
}
