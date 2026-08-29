<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkshopSurveyController extends Controller
{
    public function show(WorkshopSession $session, WorkshopSurveyTemplate $template): View|RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);

        if ($template->workshop_id !== $session->workshop_id) {
            abort(404);
        }

        $attempts = WorkshopSurveyResponse::where('workshop_survey_template_id', $template->id)
            ->where('contact_id', $contact->id)
            ->where('workshop_session_id', $session->id)
            ->count();

        if ($template->max_attempts && $attempts >= $template->max_attempts) {
            return redirect()->route('portal.my-workshops.show', $session)
                ->with('error', "You've used all {$template->max_attempts} attempt(s) for this survey.");
        }

        $questions = $template->questionsForAttempt();

        return view('portal.my-workshops.survey', compact('session', 'template', 'attempts', 'questions'));
    }

    public function submit(Request $request, WorkshopSession $session, WorkshopSurveyTemplate $template): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $this->authorizeParticipant($session, $contact->id);

        if ($template->workshop_id !== $session->workshop_id) {
            abort(404);
        }

        $responses = $request->input('responses', []);
        $presentedIds = $request->input('presented_ids', []);
        $result = $template->score($responses, $presentedIds ?: null);

        $attemptNumber = WorkshopSurveyResponse::where('workshop_survey_template_id', $template->id)
            ->where('contact_id', $contact->id)
            ->where('workshop_session_id', $session->id)
            ->count() + 1;

        WorkshopSurveyResponse::create([
            'workshop_survey_template_id' => $template->id,
            'workshop_session_id' => $session->id,
            'contact_id' => $contact->id,
            'responses_json' => $responses,
            'score' => $result['score'],
            'passed' => $result['passed'],
            'attempt_number' => $attemptNumber,
            'submitted_at' => Carbon::now(),
        ]);

        return redirect()->route('portal.my-workshops.show', $session)
            ->with('success', "Survey submitted - scored {$result['score']}%.");
    }

    private function authorizeParticipant(WorkshopSession $session, int $contactId): void
    {
        abort_unless(
            $session->participants()->where('contact_id', $contactId)->where('status', '!=', 'removed')->exists(),
            403,
        );
    }
}
