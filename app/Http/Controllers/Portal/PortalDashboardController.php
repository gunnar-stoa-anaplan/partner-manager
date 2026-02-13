<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PartnerAssignedGoal;
use App\Models\PartnerScorecard;
use App\Services\BenchmarkService;
use App\Services\PartnerHealthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortalDashboardController extends Controller
{
    public function index(PartnerHealthService $healthService, BenchmarkService $benchmarkService): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        // Scorecard
        $scorecard = PartnerScorecard::where('partner_id', $partner->id)
            ->orderByDesc('scored_at')
            ->first();

        // Health
        $health = $healthService->computeForPartner($partner);

        // Goals summary
        $goals = PartnerAssignedGoal::where('partner_id', $partner->id)
            ->orderBy('category')
            ->get();

        // Pipeline summary
        $pipeline = DB::table('opportunities')
            ->where('partner_id', $partner->id)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        // Recent deal registrations
        $dealRegs = $partner->dealRegistrations()
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('portal.dashboard', compact('contact', 'partner', 'scorecard', 'health', 'goals', 'pipeline', 'dealRegs'));
    }
}
