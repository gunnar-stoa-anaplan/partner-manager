<?php

namespace App\Services;

use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use Illuminate\Support\Collection;

class WorkshopAnalyticsService
{
    /**
     * Cross-workshop (or single-workshop, when $workshop is given) session
     * analytics: one row per session with its own pre/post/knowledge-check
     * numbers, since each workshop has its own survey templates - there's
     * no single "pre avg" that means anything across different workshops.
     * Powers both the Workshops and Sessions analytics pages.
     *
     * @return array<string, mixed>
     */
    public function sessionAnalytics(?Workshop $workshop = null): array
    {
        $sessions = WorkshopSession::query()
            ->where('status', '!=', 'archived')
            ->when($workshop, fn ($query) => $query->where('workshop_id', $workshop->id))
            ->with(['workshop:id,name,slug', 'workshop.surveyTemplates', 'participants.contact.partner:id,name'])
            ->orderBy('start_date')
            ->get();

        $sessionIds = $sessions->pluck('id');
        $responses = $sessionIds->isNotEmpty()
            ? WorkshopSurveyResponse::whereIn('workshop_session_id', $sessionIds)->get()
            : collect();

        $perSession = $sessions->map(function (WorkshopSession $session) use ($responses) {
            $sessionResponses = $responses->where('workshop_session_id', $session->id);
            $preTemplate = $session->workshop->surveyTemplates->firstWhere('kind', 'pre');
            $postTemplate = $session->workshop->surveyTemplates->firstWhere('kind', 'post');
            $knowledgeTemplate = $session->workshop->surveyTemplates->firstWhere('kind', 'knowledge');

            $preAvg = $preTemplate?->averageRating($sessionResponses->where('workshop_survey_template_id', $preTemplate->id));
            $postAvg = $postTemplate?->averageRating($sessionResponses->where('workshop_survey_template_id', $postTemplate->id));

            return [
                'session' => $session,
                'workshop' => $session->workshop,
                'participants' => $session->participants->count(),
                'pre_avg' => $preAvg,
                'post_avg' => $postAvg,
                'delta' => ($preAvg !== null && $postAvg !== null) ? round($postAvg - $preAvg, 2) : null,
                'kc_pass_rate' => $this->passRate($sessionResponses, $knowledgeTemplate),
            ];
        })->values();

        $allParticipants = $sessions->flatMap(fn (WorkshopSession $session) => $session->participants);
        $deltas = $perSession->pluck('delta')->filter(fn ($d) => $d !== null);

        return [
            'workshops_count' => $sessions->pluck('workshop_id')->unique()->count(),
            'sessions_count' => $sessions->count(),
            'participants_count' => $allParticipants->count(),
            'partner_firms_count' => $allParticipants->pluck('contact.partner_id')->filter()->unique()->count(),
            'avg_delta' => $deltas->isNotEmpty() ? round($deltas->avg(), 2) : null,
            'per_session' => $perSession,
            'top_partners' => $this->topPartners($allParticipants),
        ];
    }

    /**
     * Partner-level participation rollup: how many sessions, how many
     * people, and what share completed their session, per partner firm,
     * across the given scope (or every workshop when null).
     *
     * @return Collection<int, array{partner: string, sessions: int, participants: int, completed: int, completion_rate: int}>
     */
    public function partnerParticipation(?Workshop $workshop = null): Collection
    {
        $participants = WorkshopSessionParticipant::query()
            ->notRemoved()
            ->whereHas('session', fn ($query) => $query->where('status', '!=', 'archived')
                ->when($workshop, fn ($q) => $q->where('workshop_id', $workshop->id)))
            ->with('contact.partner:id,name')
            ->get();

        return $participants
            ->groupBy(fn ($p) => $p->contact->partner?->name ?? 'Unknown')
            ->map(function ($rows, $name) {
                $completed = $rows->whereNotNull('completed_at')->count();

                return [
                    'partner' => $name,
                    'sessions' => $rows->pluck('workshop_session_id')->unique()->count(),
                    'participants' => $rows->count(),
                    'completed' => $completed,
                    'completion_rate' => $rows->count() > 0 ? round(($completed / $rows->count()) * 100) : 0,
                ];
            })
            ->sortByDesc('participants')
            ->values();
    }

    private function passRate(Collection $sessionResponses, ?WorkshopSurveyTemplate $knowledgeTemplate): ?float
    {
        if (! $knowledgeTemplate) {
            return null;
        }

        $attempts = $sessionResponses->where('workshop_survey_template_id', $knowledgeTemplate->id);

        if ($attempts->isEmpty()) {
            return null;
        }

        return round(($attempts->where('passed', true)->count() / $attempts->count()) * 100, 1);
    }

    /**
     * @return Collection<int, array{name: string, count: int}>
     */
    private function topPartners(Collection $participants): Collection
    {
        return $participants
            ->groupBy(fn ($participant) => $participant->contact->partner?->name ?? 'Unknown')
            ->map(fn ($rows, $name) => ['name' => $name, 'count' => $rows->count()])
            ->sortByDesc('count')
            ->values();
    }
}
