<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalContactsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $contacts = Contact::where('partner_id', $partner->id)
            ->where('contact_type', '!=', 'influencer')
            ->orderBy('first_name')
            ->get();

        return view('portal.contacts.index', compact('contact', 'partner', 'contacts'));
    }

    public function create(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();

        return view('portal.contacts.create', compact('contact'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'portal_access' => 'sometimes|boolean',
            'portal_role' => ['nullable', 'string', 'in:'.implode(',', array_keys(Contact::PORTAL_ROLES))],
        ]);

        Contact::create([
            ...$validated,
            'partner_id' => $contact->partner_id,
            'contact_type' => 'general',
        ]);

        return redirect()->route('portal.contacts.index')
            ->with('success', 'Contact added successfully.');
    }

    public function edit(Contact $contact): \Illuminate\View\View
    {
        $portalUser = Auth::guard('portal')->user();
        $this->ensureSamePartner($portalUser, $contact);

        return view('portal.contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $portalUser = Auth::guard('portal')->user();
        $this->ensureSamePartner($portalUser, $contact);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'portal_access' => 'sometimes|boolean',
            'portal_role' => ['nullable', 'string', 'in:'.implode(',', array_keys(Contact::PORTAL_ROLES))],
        ]);

        $contact->update($validated);

        return redirect()->route('portal.contacts.index')
            ->with('success', $contact->full_name.' updated successfully.');
    }

    public function toggleAccess(Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $portalUser = Auth::guard('portal')->user();
        $this->ensureSamePartner($portalUser, $contact);

        if ($contact->id === $portalUser->id) {
            return back()->with('error', 'You cannot disable your own portal access.');
        }

        $contact->update(['portal_access' => ! $contact->portal_access]);
        $status = $contact->portal_access ? 'enabled' : 'disabled';

        return back()->with('success', "Portal access {$status} for {$contact->full_name}.");
    }

    public function updateRole(Request $request, Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $portalUser = Auth::guard('portal')->user();
        $this->ensureSamePartner($portalUser, $contact);

        if ($contact->id === $portalUser->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $validated = $request->validate([
            'portal_role' => ['nullable', 'string', 'in:'.implode(',', array_keys(Contact::PORTAL_ROLES))],
        ]);

        $contact->update(['portal_role' => $validated['portal_role']]);

        return back()->with('success', "Role updated for {$contact->full_name}.");
    }

    public function destroy(Contact $contact): \Illuminate\Http\RedirectResponse
    {
        $portalUser = Auth::guard('portal')->user();
        $this->ensureSamePartner($portalUser, $contact);

        if ($contact->id === $portalUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $contact->full_name;
        $contact->delete();

        return redirect()->route('portal.contacts.index')
            ->with('success', $name.' has been removed.');
    }

    private function ensureSamePartner(Contact $portalUser, Contact $contact): void
    {
        if ($contact->partner_id !== $portalUser->partner_id) {
            abort(403);
        }
    }
}
