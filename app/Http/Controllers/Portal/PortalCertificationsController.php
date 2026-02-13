<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use Illuminate\Support\Facades\Auth;

class PortalCertificationsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $certifications = Certification::where('partner_id', $partner->id)
            ->with('contact')
            ->orderByDesc('issued_date')
            ->get();

        $summary = [
            'active' => $certifications->where('status', 'active')->count(),
            'expired' => $certifications->where('status', 'expired')->count(),
            'in_progress' => $certifications->where('status', 'in_progress')->count(),
            'expiring_soon' => $certifications->filter(fn ($c) => $c->isExpiringSoon())->count(),
        ];

        return view('portal.certifications.index', compact('contact', 'partner', 'certifications', 'summary'));
    }
}
