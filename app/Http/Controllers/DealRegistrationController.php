<?php

namespace App\Http\Controllers;

use App\Models\DealRegistration;
use App\Models\Opportunity;
use App\Models\Partner;
use Illuminate\Http\Request;

class DealRegistrationController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $user = auth()->user();
        $query = DealRegistration::with(['partner', 'contact', 'reviewer']);

        if (! $user->isAdmin()) {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deals = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'submitted' => DealRegistration::pending()->count(),
            'approved' => DealRegistration::where('status', 'approved')->count(),
            'total_value' => DealRegistration::where('status', 'approved')->sum('estimated_value'),
        ];

        return view('engage.deal-registrations.index', compact('deals', 'stats'));
    }

    public function create(): \Illuminate\View\View
    {
        $user = auth()->user();
        $partners = $user->isAdmin()
            ? Partner::orderBy('name')->get(['id', 'name'])
            : Partner::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        $productAreas = DealRegistration::PRODUCT_AREAS;

        return view('engage.deal-registrations.create', compact('partners', 'productAreas'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'deal_name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_contact_email' => 'nullable|email|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date|after:today',
            'product_area' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $validated['status'] = 'submitted';
        $validated['submitted_at'] = now();
        $validated['expires_at'] = now()->addDays(90);

        DealRegistration::create($validated);

        return redirect()->route('engage.deal-registrations.index')
            ->with('success', 'Deal registration submitted successfully.');
    }

    public function show(DealRegistration $dealRegistration): \Illuminate\View\View
    {
        $dealRegistration->load(['partner', 'contact', 'opportunity', 'reviewer']);

        return view('engage.deal-registrations.show', compact('dealRegistration'));
    }

    public function review(Request $request, DealRegistration $dealRegistration): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        $dealRegistration->reviewed_by = auth()->id();
        $dealRegistration->reviewed_at = now();

        if ($validated['action'] === 'approve') {
            $dealRegistration->status = 'approved';

            // Create a linked opportunity
            $opp = Opportunity::create([
                'partner_id' => $dealRegistration->partner_id,
                'opportunity_name' => $dealRegistration->deal_name,
                'account_name' => $dealRegistration->customer_name,
                'amount' => $dealRegistration->estimated_value ?? 0,
                'close_date' => $dealRegistration->expected_close_date ?? now()->addMonths(3),
                'stage' => 'Discovery',
                'product_area' => $dealRegistration->product_area,
                'revenue_type' => 'sourced',
                'origin' => 'deal_registration',
            ]);

            $dealRegistration->opportunity_id = $opp->id;
        } else {
            $dealRegistration->status = 'rejected';
            $dealRegistration->rejection_reason = $validated['rejection_reason'];
        }

        $dealRegistration->save();

        $action = $validated['action'] === 'approve' ? 'approved' : 'rejected';

        return redirect()->back()->with('success', "Deal registration {$action}.");
    }
}
