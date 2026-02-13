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
        ]);

        Contact::create([
            ...$validated,
            'partner_id' => $contact->partner_id,
            'contact_type' => 'general',
        ]);

        return redirect()->route('portal.contacts.index')
            ->with('success', 'Contact added successfully.');
    }
}
