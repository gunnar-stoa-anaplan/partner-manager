<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\Contact;
use App\Models\PartnerAssignedGoal;
use App\Models\PartnerScorecard;
use App\Models\Project;
use App\Models\SupportCase;
use App\Services\PartnerHealthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortalDashboardController extends Controller
{
    public function index(PartnerHealthService $healthService): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $data = ['contact' => $contact, 'partner' => $partner];

        if ($contact->isPortalAdmin()) {
            $data = array_merge($data, $this->adminData($partner, $healthService));
        } elseif ($contact->isPortalSales()) {
            $data = array_merge($data, $this->salesData($partner, $contact));
        } elseif ($contact->isPortalDelivery()) {
            $data = array_merge($data, $this->deliveryData($partner, $contact));
        }

        return view('portal.dashboard', $data);
    }

    protected function adminData($partner, PartnerHealthService $healthService): array
    {
        return [
            'scorecard' => PartnerScorecard::where('partner_id', $partner->id)
                ->orderByDesc('scored_at')
                ->first(),
            'health' => $healthService->computeForPartner($partner),
            'goals' => PartnerAssignedGoal::where('partner_id', $partner->id)
                ->orderBy('category')
                ->get(),
            'pipeline' => DB::table('opportunities')
                ->where('partner_id', $partner->id)
                ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
                ->selectRaw('COUNT(*) as count, SUM(amount) as total')
                ->first(),
            'dealRegs' => $partner->dealRegistrations()
                ->orderByDesc('created_at')
                ->take(5)
                ->get(),
            'certCount' => Certification::where('partner_id', $partner->id)->where('status', 'active')->count(),
            'contactCount' => Contact::where('partner_id', $partner->id)->count(),
        ];
    }

    protected function salesData($partner, $contact): array
    {
        return [
            'pipeline' => DB::table('opportunities')
                ->where('partner_id', $partner->id)
                ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
                ->selectRaw('COUNT(*) as count, SUM(amount) as total')
                ->first(),
            'dealRegs' => $partner->dealRegistrations()
                ->orderByDesc('created_at')
                ->take(5)
                ->get(),
            'myDeals' => $contact->dealRegistrations()
                ->orderByDesc('created_at')
                ->take(5)
                ->get(),
        ];
    }

    protected function deliveryData($partner, $contact): array
    {
        return [
            'myProjects' => $contact->projects()
                ->where('partner_id', $partner->id)
                ->orderByDesc('created_at')
                ->get(),
            'openCases' => SupportCase::where('partner_id', $partner->id)
                ->open()
                ->orderByDesc('opened_date')
                ->take(5)
                ->get(),
            'allProjects' => Project::where('partner_id', $partner->id)->get(),
        ];
    }
}
