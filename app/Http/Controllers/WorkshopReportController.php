<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Services\WorkshopLeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class WorkshopReportController extends Controller
{
    public function __construct(private WorkshopLeaderboardService $leaderboardService) {}

    public function index(Workshop $workshop, WorkshopSession $session): View
    {
        $data = $this->sessionReportData($session);
        $partners = $session->participants->pluck('contact.partner')->filter()->unique('id')->values();

        return view('workshops.reports.index', [...$data, 'partners' => $partners]);
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
        $lines[] = "*{$data['workshop']->name} - {$data['session']->name}*";
        $lines[] = "{$data['session']->start_date->format('M j')} - {$data['session']->end_date->format('M j, Y')}";
        $lines[] = '';
        $lines[] = "> {$data['total']} invited, {$data['completed']} completed ({$data['completionRate']}%)";

        if ($data['surveyTotal'] > 0) {
            $lines[] = "> Post-workshop survey: {$data['surveyPassed']}/{$data['surveyTotal']} passed, avg score {$data['surveyAvgScore']}%";
        }

        $lines[] = "> {$data['openThreads']} open question(s), {$data['answeredThreads']} answered";

        if ($data['partnerBreakdown']->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '*By partner:*';

            foreach ($data['partnerBreakdown'] as $row) {
                $lines[] = "- {$row['partner']}: {$row['completed']}/{$row['total']} completed";
            }
        }

        $text = implode("\n", $lines);

        return view('workshops.reports.slack', ['text' => $text, 'workshop' => $workshop, 'session' => $session]);
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
            'workshop',
            'participants' => fn ($q) => $q->notRemoved()->with('contact.partner:id,name'),
            'surveyResponses',
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

        return [
            'workshop' => $session->workshop,
            'session' => $session,
            'participants' => $participants,
            'total' => $total,
            'completed' => $completed,
            'completionRate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'surveyTotal' => $surveyResponses->count(),
            'surveyPassed' => $surveyResponses->where('passed', true)->count(),
            'surveyAvgScore' => $surveyResponses->isNotEmpty() ? round($surveyResponses->avg('score')) : 0,
            'openThreads' => $session->threads->where('status', 'open')->count(),
            'answeredThreads' => $session->threads->where('status', 'answered')->count(),
            'partnerBreakdown' => $partnerBreakdown,
        ];
    }
}
