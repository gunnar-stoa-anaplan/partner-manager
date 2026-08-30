<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyResponse;
use App\Services\WorkshopLeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class WorkshopReportController extends Controller
{
    public function __construct(private WorkshopLeaderboardService $leaderboardService) {}

    public function partners(Workshop $workshop, WorkshopSession $session): View
    {
        $rows = $this->partnerCompletionRows($session);

        return view('workshops.reports.partners', compact('workshop', 'session', 'rows'));
    }

    private function hasAllThreeSurveys(Collection $responses, int $contactId): bool
    {
        $kinds = collect($responses->where('contact_id', $contactId)->map(fn ($r) => $r->template?->kind)->filter()->unique()->all());

        return $kinds->intersect(['pre', 'post', 'knowledge'])->count() === 3;
    }

    public function index(Workshop $workshop, WorkshopSession $session): View
    {
        $data = $this->sessionReportData($session);

        return view('workshops.reports.index', $data);
    }

    public function execSummary(Workshop $workshop, WorkshopSession $session): View
    {
        $data = $this->sessionReportData($session);

        return view('workshops.reports.exec-summary', $data);
    }

    public function slackPost(Workshop $workshop, WorkshopSession $session): View
    {
        $data = $this->sessionReportData($session);

        $lines = [];
        $lines[] = "🎉 {$data['workshop']->name} — {$data['session']->name} just wrapped!";
        $lines[] = '';
        $lines[] = "{$data['partnersCount']} delivery partners sent {$data['total']} consultants through the workshop this week. Quick highlights:";

        if ($data['preAvg'] !== null || $data['postAvg'] !== null) {
            $lines[] = '';
            $lines[] = '📈 Learning progression';
            $lines[] = '• Pre-workshop survey average: '.($data['preAvg'] ?? '—');
            $lines[] = '• Post-workshop survey average: '.($data['postAvg'] ?? '—');

            if ($data['prePostDelta'] !== null) {
                $biggestGain = $this->biggestLikertGain($session);
                $gainNote = $biggestGain ? " — biggest gains in \"{$biggestGain}\"" : '';
                $lines[] = '• Net improvement: '.($data['prePostDelta'] >= 0 ? '+' : '').$data['prePostDelta']." points{$gainNote}";
            }
        }

        $topPartners = $this->partnerCompletionRows($session)->take(3);

        if ($topPartners->isNotEmpty()) {
            $medals = ['🥇', '🥈', '🥉'];
            $lines[] = '';
            $lines[] = '🏆 Top completion rates by partner';

            foreach ($topPartners as $index => $row) {
                $lines[] = ($medals[$index] ?? '•')." {$row['partner']} — {$row['all_three_done']}/{$row['sent']} all three milestones ({$row['completion_rate']}%)";
            }
        }

        $lines[] = '';
        $lines[] = "💬 {$data['openThreads']} open question(s), {$data['answeredThreads']} answered during the week.";

        $text = implode("\n", $lines);

        return view('workshops.reports.slack', ['text' => $text, 'workshop' => $workshop, 'session' => $session]);
    }

    private function partnerCompletionRows(WorkshopSession $session): Collection
    {
        $session->loadMissing(['participants.contact.partner:id,name']);
        $responses = WorkshopSurveyResponse::where('workshop_session_id', $session->id)->with('template:id,kind')->get();

        return $session->participants
            ->groupBy(fn ($p) => $p->contact->partner_id ?? 0)
            ->map(function ($participants) use ($responses) {
                $partner = $participants->first()->contact->partner;
                $contactIds = $participants->pluck('contact_id');
                $allThreeDone = $contactIds->filter(fn ($id) => $this->hasAllThreeSurveys($responses, $id))->count();

                return [
                    'partner' => $partner?->name ?? 'Unknown',
                    'partner_model' => $partner,
                    'sent' => $participants->count(),
                    'all_three_done' => $allThreeDone,
                    'completion_rate' => $participants->count() > 0 ? round(($allThreeDone / $participants->count()) * 100) : 0,
                ];
            })
            ->sortByDesc('completion_rate')
            ->values();
    }

    /**
     * The likert question (shared between the pre and post templates) with
     * the largest positive average delta, for the Slack post's highlight.
     */
    private function biggestLikertGain(WorkshopSession $session): ?string
    {
        $workshop = $session->workshop()->with('surveyTemplates')->first();
        $preTemplate = $workshop->surveyTemplates->firstWhere('kind', 'pre');
        $postTemplate = $workshop->surveyTemplates->firstWhere('kind', 'post');

        if (! $preTemplate || ! $postTemplate) {
            return null;
        }

        $preResponses = WorkshopSurveyResponse::where('workshop_session_id', $session->id)->where('workshop_survey_template_id', $preTemplate->id)->get();
        $postResponses = WorkshopSurveyResponse::where('workshop_session_id', $session->id)->where('workshop_survey_template_id', $postTemplate->id)->get();

        $questions = collect($preTemplate->questions_json)->where('type', 'likert')->keyBy('id');
        $postQuestionIds = collect($postTemplate->questions_json)->where('type', 'likert')->pluck('id');

        $best = $questions->keys()->intersect($postQuestionIds)->map(function ($questionId) use ($questions, $preResponses, $postResponses) {
            $preValues = $preResponses->map(fn ($r) => $r->responses_json[$questionId] ?? null)->filter(fn ($v) => is_numeric($v));
            $postValues = $postResponses->map(fn ($r) => $r->responses_json[$questionId] ?? null)->filter(fn ($v) => is_numeric($v));

            if ($preValues->isEmpty() || $postValues->isEmpty()) {
                return null;
            }

            return ['text' => $questions[$questionId]['text'], 'delta' => $postValues->avg() - $preValues->avg()];
        })->filter()->sortByDesc('delta')->first();

        return $best['text'] ?? null;
    }

    public function exportJson(Workshop $workshop, WorkshopSession $session): JsonResponse
    {
        $data = $this->sessionReportData($session);

        return response()->json([
            'workshop' => $data['workshop']->only(['id', 'name', 'slug']),
            'session' => $data['session']->only(['id', 'name', 'slug', 'start_date', 'end_date', 'status']),
            'totals' => [
                'invited' => $data['total'],
                'completed' => $data['completed'],
                'completion_rate' => $data['completionRate'],
            ],
            'survey' => [
                'total' => $data['surveyTotal'],
                'passed' => $data['surveyPassed'],
                'average_score' => $data['surveyAvgScore'],
            ],
            'discussion' => [
                'open_threads' => $data['openThreads'],
                'answered_threads' => $data['answeredThreads'],
            ],
            'by_partner' => $data['partnerBreakdown']->values(),
            'participants' => $data['participants']->map(fn ($p) => [
                'name' => $p->contact->full_name,
                'partner' => $p->contact->partner?->name,
                'status' => $p->status,
                'completed' => $p->completed_at !== null,
            ])->values(),
        ]);
    }

    public function partnerReport(Workshop $workshop, WorkshopSession $session, Partner $partner): View
    {
        $data = $this->sessionReportData($session, $partner->id);

        return view('workshops.reports.partner-sponsor', [...$data, 'partner' => $partner]);
    }

    public function partnerReportZip(Workshop $workshop, WorkshopSession $session, Partner $partner): BinaryFileResponse
    {
        $data = $this->sessionReportData($session, $partner->id);
        $html = view('workshops.reports.partner-sponsor', [...$data, 'partner' => $partner])->render();

        $zipPath = tempnam(sys_get_temp_dir(), 'workshop-report').'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('report.html', $html);
        $zip->addFromString('summary.txt', "{$workshop->name} - {$session->name}\n{$partner->name}\n\n{$data['completed']}/{$data['total']} participants completed ({$data['completionRate']}%)");
        $zip->close();

        $filename = \Illuminate\Support\Str::slug("{$workshop->slug}-{$session->slug}-{$partner->name}").'.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    public function partnerReportsZipAll(Workshop $workshop, WorkshopSession $session): BinaryFileResponse
    {
        $session->load(['participants.contact.partner']);
        $partners = $session->participants->pluck('contact.partner')->filter()->unique('id')->values();

        $zipPath = tempnam(sys_get_temp_dir(), 'workshop-reports').'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($partners as $partner) {
            $data = $this->sessionReportData($session, $partner->id);
            $html = view('workshops.reports.partner-sponsor', [...$data, 'partner' => $partner])->render();
            $filename = \Illuminate\Support\Str::slug("{$workshop->slug}-{$session->slug}-{$partner->name}").'.html';
            $zip->addFromString($filename, $html);
        }

        $zip->close();

        $filename = \Illuminate\Support\Str::slug("{$workshop->slug}-{$session->slug}-partner-reports").'.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    public function sessionLeaderboard(Workshop $workshop, WorkshopSession $session): View
    {
        $session->load(['participants' => fn ($q) => $q->notRemoved()->with('contact.partner:id,name')]);

        $checkpoints = $this->leaderboardService->checkpointsFor($workshop);
        [$surveyResponses, $checkAttempts] = $this->leaderboardService->loadDataForSession($session->id);
        $checkpointRowsByContact = collect($this->leaderboardService->rowsFor($session->participants, $checkpoints, $surveyResponses, $checkAttempts))
            ->keyBy(fn ($row) => $row['contact']->id);

        $leaderboard = $session->participants
            ->map(fn ($participant) => [
                'contact' => $participant->contact,
                'statuses' => $checkpointRowsByContact->get($participant->contact_id)['statuses'] ?? [],
            ])
            ->sortBy([
                fn ($row) => $row['contact']->partner?->name ?? 'zzz',
                fn ($row) => $row['contact']->full_name,
            ])
            ->values();

        return view('workshops.sessions.leaderboard', compact('workshop', 'session', 'leaderboard', 'checkpoints'));
    }

    /**
     * @return array<string, mixed>
     */
    private function sessionReportData(WorkshopSession $session, ?int $partnerId = null): array
    {
        $session->load([
            'workshop.surveyTemplates',
            'participants' => fn ($q) => $q->notRemoved()->with('contact.partner:id,name'),
            'surveyResponses.template',
            'threads',
        ]);

        $participants = $partnerId
            ? $session->participants->filter(fn ($p) => $p->contact->partner_id === $partnerId)->values()
            : $session->participants;

        $total = $participants->count();
        $completed = $participants->whereNotNull('completed_at')->count();

        $surveyResponses = $partnerId
            ? $session->surveyResponses->filter(fn ($r) => $r->contact?->partner_id === $partnerId)
            : $session->surveyResponses;

        $partnerBreakdown = $session->participants
            ->groupBy(fn ($p) => $p->contact->partner?->name ?? 'Unknown')
            ->map(fn ($rows, $name) => [
                'partner' => $name,
                'total' => $rows->count(),
                'completed' => $rows->whereNotNull('completed_at')->count(),
            ])
            ->sortByDesc('total');

        $preTemplate = $session->workshop->surveyTemplates->firstWhere('kind', 'pre');
        $postTemplate = $session->workshop->surveyTemplates->firstWhere('kind', 'post');
        $preAvg = $preTemplate?->averageRating($surveyResponses->where('workshop_survey_template_id', $preTemplate->id));
        $postAvg = $postTemplate?->averageRating($surveyResponses->where('workshop_survey_template_id', $postTemplate->id));

        $allThreeDoneCount = $participants->pluck('contact_id')->filter(fn ($id) => $this->hasAllThreeSurveys($surveyResponses, $id))->count();

        return [
            'workshop' => $session->workshop,
            'session' => $session,
            'participants' => $participants,
            'total' => $total,
            'completed' => $completed,
            'completionRate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'partnersCount' => $partnerBreakdown->count(),
            'allThreeDoneCount' => $allThreeDoneCount,
            'allThreeDoneRate' => $total > 0 ? round(($allThreeDoneCount / $total) * 100) : 0,
            'preAvg' => $preAvg,
            'postAvg' => $postAvg,
            'prePostDelta' => ($preAvg !== null && $postAvg !== null) ? round($postAvg - $preAvg, 2) : null,
            'surveyTotal' => $surveyResponses->count(),
            'surveyPassed' => $surveyResponses->where('passed', true)->count(),
            'surveyAvgScore' => $surveyResponses->isNotEmpty() ? round($surveyResponses->avg('score')) : 0,
            'openThreads' => $session->threads->where('status', 'open')->count(),
            'answeredThreads' => $session->threads->where('status', 'answered')->count(),
            'partnerBreakdown' => $partnerBreakdown,
        ];
    }
}
