<?php

namespace App\Http\Controllers;

use App\Models\DealRegistration;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Support\DatabasePortability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DealRegistrationController extends Controller
{
    public function dashboard(): \Illuminate\View\View
    {
        $user = auth()->user();
        $partnerIds = $user->isAdmin() ? null : $user->partners()->pluck('id');

        $scoped = fn () => $partnerIds
            ? DealRegistration::whereIn('partner_id', $partnerIds)
            : DealRegistration::query();

        // ── KPI Cards ──
        $totalDeals = $scoped()->count();
        $pendingDeals = $scoped()->where('status', 'submitted')->count();
        $approvedDeals = $scoped()->where('status', 'approved')->count();
        $rejectedDeals = $scoped()->where('status', 'rejected')->count();
        $totalPipelineValue = $scoped()->whereIn('status', ['submitted', 'approved'])->sum('estimated_value');
        $approvedValue = $scoped()->where('status', 'approved')->sum('estimated_value');
        $avgDealSize = $totalDeals > 0 ? round($scoped()->avg('estimated_value'), 0) : 0;

        // ── Deals by Status (donut) ──
        $dealsByStatus = $scoped()->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Deals by Product Area (bar) ──
        $dealsByProductArea = $scoped()->select('product_area', DB::raw('count(*) as total'))
            ->whereNotNull('product_area')
            ->groupBy('product_area')
            ->pluck('total', 'product_area');

        // ── Pipeline Value by Product Area (bar) ──
        $valueByProductArea = $scoped()->select('product_area', DB::raw('sum(estimated_value) as total'))
            ->whereNotNull('product_area')
            ->where('status', '!=', 'rejected')
            ->groupBy('product_area')
            ->pluck('total', 'product_area');

        // ── Deals by Month (area chart) ──
        $dealsByMonth = $scoped()->select(
            DB::raw(DatabasePortability::dateFormat('submitted_at', '%Y-%m').' as month'),
            DB::raw('count(*) as total')
        )
            ->whereNotNull('submitted_at')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // ── Top Partners by Deal Count ──
        $topPartners = $scoped()->select('partner_id', DB::raw('count(*) as total'), DB::raw('sum(estimated_value) as value'))
            ->groupBy('partner_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('partner:id,name')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->partner->name ?? 'Unknown',
                'count' => $row->total,
                'value' => $row->value,
            ]);

        // ── Recent Deals Table ──
        $recentDeals = $scoped()->with(['partner:id,name', 'reviewer:id,name'])
            ->latest()
            ->limit(15)
            ->get();

        return view('engage.deal-registrations.dashboard', compact(
            'totalDeals',
            'pendingDeals',
            'approvedDeals',
            'rejectedDeals',
            'totalPipelineValue',
            'approvedValue',
            'avgDealSize',
            'dealsByStatus',
            'dealsByProductArea',
            'valueByProductArea',
            'dealsByMonth',
            'topPartners',
            'recentDeals',
        ));
    }

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
