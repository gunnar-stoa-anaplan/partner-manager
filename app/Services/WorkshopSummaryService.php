<?php

namespace App\Services;

use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyResponse;
use App\Models\WorkshopSurveyTemplate;
use Illuminate\Support\Collection;

class WorkshopSummaryService
{
    /**
     * A condensed cross-session rollup for the workshop detail page: overall
     * tiles, a per-session progression table, and top partners by
     * participant volume. Scoped to non-archived sessions only, since an
     * archived session is retired and shouldn't skew the live picture.
     *
     * @return array<string, mixed>
     */
    public function build(Workshop $workshop): array
    {
        $sessions = $workshop->sessions()
            ->where('status', '!=', 'archived')
            ->with('participants.contact.partner:id,name')
            ->orderBy('start_date')
            ->get();

        $preTemplate = $workshop->surveyTemplates->firstWhere('kind', 'pre');
        $postTemplate = $workshop->surveyTemplates->firstWhere('kind', 'post');

        $responses = $sessions->isNotEmpty()
            ? WorkshopSurveyResponse::whereIn('workshop_session_id', $sessions->pluck('id'))->get()
            : collect();

        $perSession = $sessions->map(function (WorkshopSession $session) use ($responses, $preTemplate, $postTemplate) {
            $sessionResponses = $responses->where('workshop_session_id', $session->id);

            return [
                'session' => $session,
                'participants' => $session->participants->count(),
                'pre_avg' => $preTemplate?->averageRating($sessionResponses->where('workshop_survey_template_id', $preTemplate->id)),
                'post_avg' => $postTemplate?->averageRating($sessionResponses->where('workshop_survey_template_id', $postTemplate->id)),
                'kc_pass_rate' => $this->knowledgeCheckPassRate($sessionResponses, $postTemplate),
            ];
        });

        $perSession = $perSession->map(function (array $row) {
            $row['delta'] = ($row['pre_avg'] !== null && $row['post_avg'] !== null)
                ? round($row['post_avg'] - $row['pre_avg'], 2)
                : null;

            return $row;
        });

        $allParticipants = $sessions->flatMap(fn (WorkshopSession $session) => $session->participants);

        return [
            'sessions_count' => $sessions->count(),
            'date_range' => $sessions->isNotEmpty()
                ? ['start' => $sessions->first()->start_date, 'end' => $sessions->last()->end_date]
                : null,
            'participants_count' => $allParticipants->count(),
            'partner_firms_count' => $allParticipants->pluck('contact.partner_id')->filter()->unique()->count(),
            'pre_avg' => $preTemplate?->averageRating($responses->where('workshop_survey_template_id', $preTemplate->id)),
            'post_avg' => $postTemplate?->averageRating($responses->where('workshop_survey_template_id', $postTemplate->id)),
            'per_session' => $perSession->values(),
            'top_partners' => $this->topPartners($allParticipants),
        ];
    }

    private function knowledgeCheckPassRate(Collection $sessionResponses, ?WorkshopSurveyTemplate $postTemplate): ?float
    {
        if (! $postTemplate || ! $postTemplate->isScored()) {
            return null;
        }

        $attempts = $sessionResponses->where('workshop_survey_template_id', $postTemplate->id);

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
