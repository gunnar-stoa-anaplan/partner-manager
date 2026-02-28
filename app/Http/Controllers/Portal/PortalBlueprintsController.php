<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BlueprintEnrollment;
use App\Models\PartnerBlueprintAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalBlueprintsController extends Controller
{
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $assignments = PartnerBlueprintAssignment::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->with(['blueprint', 'enrollments.contact'])
            ->get();

        // Contact's own enrollments
        $myEnrollments = BlueprintEnrollment::where('contact_id', $contact->id)
            ->with(['assignment.blueprint'])
            ->get();

        return view('portal.blueprints.index', compact('contact', 'partner', 'assignments', 'myEnrollments'));
    }

    public function showAssignment(PartnerBlueprintAssignment $assignment): View
    {
        $contact = Auth::guard('portal')->user();

        abort_unless($assignment->partner_id === $contact->partner_id, 403);

        $assignment->load([
            'blueprint.outcomes.contentItems',
            'enrollments.contact',
            'enrollments.progress',
        ]);

        return view('portal.blueprints.assignment', compact('contact', 'assignment'));
    }

    public function show(BlueprintEnrollment $enrollment): View
    {
        $contact = Auth::guard('portal')->user();

        // Ensure enrollment belongs to this partner
        $enrollment->load(['assignment.blueprint.outcomes.contentItems', 'progress', 'contact']);
        abort_unless($enrollment->assignment->partner_id === $contact->partner_id, 403);

        // Non-admin contacts can only view their own enrollments
        if (! $contact->isPortalAdmin() && $enrollment->contact_id !== $contact->id) {
            abort(403);
        }

        return view('portal.blueprints.show', compact('contact', 'enrollment'));
    }
}
