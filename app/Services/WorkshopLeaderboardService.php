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
     * one column per individual lab check (not per page - a page can bundle
     * several "check my work" prompts and each is its own pass/fail signal),
     * post-workshop survey, then the knowledge check.
     *
     * @return array<int, array{key: string, label: string, type: 'survey'|'lab', template_id?: int, page_id?: int, check_id?: string}>
     */
    public function checkpointsFor(Workshop $workshop): array
    {
        $checkpoints = [];

        $preSurvey = $workshop->surveyTemplates->firstWhere('kind', 'pre');
        if ($preSurvey && ! empty($preSurvey->questions_json)) {
            $checkpoints[] = ['key' => 'survey_pre', 'label' => 'Pre-Workshop Survey', 'type' => 'survey', 'template_id' => $preSurvey->id];
        }

        foreach ($workshop->participantPages as $page) {
            foreach ($page->checks() as $check) {
                $checkpoints[] = [
                    'key' => 'lab_'.$page->id.'_'.$check['id'],
                    'label' => $this->shortCheckLabel($check['id']),
                    'type' => 'lab',
                    'page_id' => $page->id,
                    'check_id' => $check['id'],
                ];
            }
        }

        $postSurvey = $workshop->surveyTemplates->firstWhere('kind', 'post');
        if ($postSurvey && ! empty($postSurvey->questions_json)) {
            $checkpoints[] = ['key' => 'survey_post', 'label' => 'Post-Workshop Survey', 'type' => 'survey', 'template_id' => $postSurvey->id];
        }

        $knowledgeCheck = $workshop->surveyTemplates->firstWhere('kind', 'knowledge');
        if ($knowledgeCheck && ! empty($knowledgeCheck->questions_json)) {
            $checkpoints[] = ['key' => 'survey_knowledge', 'label' => 'Knowledge Check', 'type' => 'survey', 'template_id' => $knowledgeCheck->id];
        }

        return $checkpoints;
    }

    /**
     * Per-participant status ('done' | 'partial' | 'failed' | 'todo') for
     * every checkpoint, keyed by checkpoint key. $surveyResponses and
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
        $attempts = $contactResponses->where('workshop_survey_template_id', $checkpoint['template_id']);

        if ($attempts->isEmpty()) {
            return 'todo';
        }

        $latest = $attempts->sortByDesc('attempt_number')->first();
        $template = $latest->template;

        // A plain (unscored) survey has no pass/fail concept - submitting it
        // is the whole checkpoint.
        if (! $template?->isScored()) {
            return 'done';
        }

        if ($attempts->where('passed', true)->isNotEmpty()) {
            return 'done';
        }

        // Still has attempts left (or unlimited) - in progress, not failed yet.
        if (! $template->max_attempts || $latest->attempt_number < $template->max_attempts) {
            return 'partial';
        }

        return 'failed';
    }

    /**
     * A single lab check is inherently binary once attempted - it either
     * has a passing attempt or it doesn't, so unlike a multi-attempt survey
     * there's no separate "in progress" state here.
     *
     * @param  array<string, mixed>  $checkpoint
     */
    private function labStatus(array $checkpoint, Collection $contactAttempts): string
    {
        $attempts = $contactAttempts
            ->where('workshop_page_id', $checkpoint['page_id'])
            ->where('check_id', $checkpoint['check_id']);

        if ($attempts->isEmpty()) {
            return 'todo';
        }

        return $attempts->where('passed', true)->isNotEmpty() ? 'done' : 'failed';
    }

    /**
     * Derive a short column-header label from a check id like
     * "ex1-step2-dimensions" -> "EX1: DIMENSIONS", since the checks only
     * carry a long instructional prompt, not a leaderboard-ready label.
     */
    private function shortCheckLabel(string $checkId): string
    {
        if (preg_match('/^ex(\d+)-step\d+-(.+)$/', $checkId, $matches)) {
            return 'EX'.$matches[1].': '.strtoupper(str_replace(['-', '_'], ' ', $matches[2]));
        }

        return strtoupper(str_replace(['-', '_'], ' ', $checkId));
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
