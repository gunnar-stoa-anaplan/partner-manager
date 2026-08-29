<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkshopParticipationController extends Controller
{
    /**
     * Sessions this contact has personally been invited to.
     */
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();

        $sessions = $contact->workshopSessions()
            ->wherePivot('status', '!=', 'removed')
            ->with('workshop')
            ->orderBy('start_date')
            ->get();

        return view('portal.my-workshops.index', compact('sessions'));
    }

    public function show(WorkshopSession $session): View
    {
        $contact = Auth::guard('portal')->user();

        abort_unless(
            $session->participants()->where('contact_id', $contact->id)->where('status', '!=', 'removed')->exists(),
            403,
        );

        $session->load([
            'workshop',
            'workshop.surveyTemplates',
            'threads' => fn ($query) => $query->where('status', '!=', 'hidden')->latest(),
            'threads.replies',
        ]);

        $myCompletedSurveyKinds = $session->surveyResponses()
            ->where('contact_id', $contact->id)
            ->pluck('workshop_survey_template_id');

        return view('portal.my-workshops.show', compact('session', 'myCompletedSurveyKinds'));
    }
}
