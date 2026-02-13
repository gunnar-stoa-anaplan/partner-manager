<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\DealRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalDealRegistrationController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $deals = DealRegistration::where('partner_id', $partner->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('portal.deal-registrations.index', compact('contact', 'partner', 'deals'));
    }

    public function create(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $productAreas = DealRegistration::PRODUCT_AREAS;

        return view('portal.deal-registrations.create', compact('contact', 'productAreas'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        $validated = $request->validate([
            'deal_name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_contact_email' => 'nullable|email|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date|after:today',
            'product_area' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $validated['partner_id'] = $contact->partner_id;
        $validated['contact_id'] = $contact->id;
        $validated['status'] = 'submitted';
        $validated['submitted_at'] = now();
        $validated['expires_at'] = now()->addDays(90);

        DealRegistration::create($validated);

        return redirect()->route('portal.deal-registrations.index')
            ->with('success', 'Deal registration submitted for review.');
    }
}
