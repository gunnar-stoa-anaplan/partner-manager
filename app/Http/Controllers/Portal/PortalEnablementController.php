<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\EnablementRegistration;
use App\Models\EnablementSession;
use Illuminate\Support\Facades\Auth;

class PortalEnablementController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        // Contact's own registrations
        $myRegistrations = EnablementRegistration::where('contact_id', $contact->id)
            ->with(['session.event'])
            ->orderByDesc('created_at')
            ->get();

        // Upcoming sessions relevant to this partner
        $upcomingSessions = EnablementSession::whereHas('event', function ($q) use ($contact) {
            $q->where('status', 'published');

            // Filter by role relevance
            if ($contact->isPortalSales()) {
                $q->whereIn('category', ['Sales', 'Pre-Sales', 'Awareness']);
            } elseif ($contact->isPortalDelivery()) {
                $q->whereIn('category', ['Delivery', 'Platform', 'Awareness']);
            }
            // Admin sees all
        })
            ->where('start_at', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->with('event')
            ->orderBy('start_at')
            ->take(10)
            ->get();

        return view('portal.enablement.index', compact('contact', 'partner', 'myRegistrations', 'upcomingSessions'));
    }
}
