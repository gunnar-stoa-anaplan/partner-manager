<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Workshop;
use App\Models\WorkshopCheckAttempt;
use App\Models\WorkshopSurveyResponse;
use Illuminate\Support\Collection;

class WorkshopLeaderboardService
{
    /**
     * Ordered checkpoints for a workshop's leaderboard: pre-workshop survey,
     * one column per content page that has "check my work" prompts (labs),
     * then the post-workshop survey - labeled "Knowledge Check" when it's
     * scored (has a passing_score), since that's what it functionally is.
     *
     * @return array<int, array{key: string, label: string, type: 'survey'|'lab', template_id?: int, page_id?: int}>
     */
    public function checkpointsFor(Workshop $workshop): array
    {
        $checkpoints = [];

        $preSurvey = $workshop->surveyTemplates->firstWhere('kind', 'pre');
        if ($preSurvey) {
            $checkpoints[] = ['key' => 'survey_pre', 'label' => 'Pre-Workshop Survey', 'type' => 'survey', 'template_id' => $preSurvey->id];
        }

        foreach ($workshop->participantPages as $page) {
            $checkCount = count($page->checks());
            if ($checkCount > 0) {
                $checkpoints[] = ['key' => 'lab_'.$page->id, 'label' => $page->title, 'type' => 'lab', 'page_id' => $page->id, 'total_checks' => $checkCount];
            }
        }

        $postSurvey = $workshop->surveyTemplates->firstWhere('kind', 'post');
        if ($postSurvey) {
            $label = $postSurvey->isScored() ? 'Knowledge Check' : 'Post-Workshop Survey';
            $checkpoints[] = ['key' => 'survey_post', 'label' => $label, 'type' => 'survey', 'template_id' => $postSurvey->id];
        }

        return $checkpoints;
    }

    /**
     * Per-participant status ('done' | 'partial' | 'todo') for every
     * checkpoint, keyed by checkpoint key. $surveyResponses and
     * $checkAttempts should already be scoped to the session(s) being
     * evaluated and loaded once by the caller to avoid N+1 queries.
     *
     * @param  array<int, array<string, mixed>>  $checkpoints
     * @return array<int, array{contact: Contact, statuses: array<string, string>}>
     */
    public function rowsFor(Collection $participants, array $checkpoints, Collection $surveyResponses, Collection $checkAttempts): array
    {
        $responsesByContact = $surveyResponses->groupBy('contact_id');
        $attemptsByContact = $checkAttempts->groupBy('contact_id');

        return $participants->map(function ($participant) use ($checkpoints, $responsesByContact, $attemptsByContact) {
            $contact = $participant->contact;
            $contactResponses = $responsesByContact->get($contact->id, collect());
            $contactAttempts = $attemptsByContact->get($contact->id, collect());

            $statuses = [];
            foreach ($checkpoints as $checkpoint) {
                $statuses[$checkpoint['key']] = $checkpoint['type'] === 'lab'
                    ? $this->labStatus($checkpoint, $contactAttempts)
                    : $this->surveyStatus($checkpoint, $contactResponses);
            }

            return ['contact' => $contact, 'statuses' => $statuses];
        })->all();
    }

    /**
     * @param  array<string, mixed>  $checkpoint
     */
    private function surveyStatus(array $checkpoint, Collection $contactResponses): string
    {
        $response = $contactResponses->firstWhere('workshop_survey_template_id', $checkpoint['template_id']);

        if (! $response) {
            return 'todo';
        }

        // A plain (unscored) survey has no pass/fail concept - submitting it
        // is the whole checkpoint. A scored one (knowledge check) needs a
        // passing result to count as fully done.
        if ($response->template?->isScored()) {
            return $response->passed ? 'done' : 'partial';
        }

        return 'done';
    }

    /**
     * @param  array<string, mixed>  $checkpoint
     */
    private function labStatus(array $checkpoint, Collection $contactAttempts): string
    {
        $pageAttempts = $contactAttempts->where('workshop_page_id', $checkpoint['page_id']);

        if ($pageAttempts->isEmpty()) {
            return 'todo';
        }

        $passedCheckIds = $pageAttempts->where('passed', true)->pluck('check_id')->unique();

        if ($passedCheckIds->isEmpty()) {
            return 'partial';
        }

        return $passedCheckIds->count() >= $checkpoint['total_checks'] ? 'done' : 'partial';
    }

    /**
     * Bulk-load survey responses and check attempts for one session, ready
     * to pass into rowsFor() without any further per-participant queries.
     *
     * @return array{0: Collection<int, WorkshopSurveyResponse>, 1: Collection<int, WorkshopCheckAttempt>}
     */
    public function loadDataForSession(int $sessionId): array
    {
        return $this->loadDataForSessions([$sessionId]);
    }

    /**
     * Same as loadDataForSession(), scoped to every session in a workshop -
     * for the workshop-wide rollup leaderboard, where a participant's data
     * may live under whichever session they're actually enrolled in.
     *
     * @param  array<int, int>  $sessionIds
     * @return array{0: Collection<int, WorkshopSurveyResponse>, 1: Collection<int, WorkshopCheckAttempt>}
     */
    public function loadDataForSessions(array $sessionIds): array
    {
        return [
            WorkshopSurveyResponse::whereIn('workshop_session_id', $sessionIds)->with('template')->get(),
            WorkshopCheckAttempt::whereIn('workshop_session_id', $sessionIds)->get(),
        ];
    }
}
