<?php

namespace App\Console\Commands;

use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyResponse;
use App\Notifications\WorkshopDailyParticipationDigest;
use App\Notifications\WorkshopSessionReminder;
use App\Notifications\WorkshopSurveyPendingReminder;
use App\Services\WorkshopLeaderboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendWorkshopReminders extends Command
{
    protected $signature = 'workshops:send-reminders';

    protected $description = 'Send session-starting reminders, pending-survey reminders, and daily participation digests for active workshop sessions';

    public function __construct(private WorkshopLeaderboardService $leaderboardService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $sessionReminders = $this->sendSessionReminders();
        $surveyReminders = $this->sendSurveyPendingReminders();
        $digests = $this->sendDailyDigests();

        $this->info("Sent {$sessionReminders} session reminder(s), {$surveyReminders} survey reminder(s), {$digests} daily digest(s).");

        return self::SUCCESS;
    }

    /**
     * Nudges everyone enrolled in a session starting exactly 3 days out.
     */
    private function sendSessionReminders(): int
    {
        $targetDate = Carbon::today()->addDays(3);

        $sessions = WorkshopSession::where('status', 'upcoming')
            ->whereDate('start_date', $targetDate)
            ->get();

        $count = 0;

        foreach ($sessions as $session) {
            $contacts = $session->participants()->notRemoved()->with('contact')->get()->pluck('contact')->filter();

            foreach ($contacts as $contact) {
                $contact->notify(new WorkshopSessionReminder($session));
                $count++;
            }
        }

        return $count;
    }

    /**
     * Nudges anyone enrolled in an upcoming or in-progress session who
     * hasn't submitted a response for one of the workshop's live survey
     * templates yet.
     */
    private function sendSurveyPendingReminders(): int
    {
        $sessions = WorkshopSession::whereIn('status', ['upcoming', 'in_progress'])
            ->with('workshop.surveyTemplates')
            ->get();

        $count = 0;

        foreach ($sessions as $session) {
            $participants = $session->participants()->notRemoved()->with('contact')->get();

            foreach ($session->workshop->surveyTemplates as $template) {
                $respondedContactIds = WorkshopSurveyResponse::where('workshop_survey_template_id', $template->id)
                    ->where('workshop_session_id', $session->id)
                    ->pluck('contact_id');

                foreach ($participants as $participant) {
                    if (! $participant->contact_id || $respondedContactIds->contains($participant->contact_id)) {
                        continue;
                    }

                    $participant->contact->notify(new WorkshopSurveyPendingReminder($session, $template));
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Sends each session's assigned monitors a same-day participation
     * summary while the session is actively running.
     */
    private function sendDailyDigests(): int
    {
        $sessions = WorkshopSession::where('status', 'in_progress')->get();
        $count = 0;

        foreach ($sessions as $session) {
            $monitorUsers = $session->monitors()->whereNull('removed_at')->with('user')->get()->pluck('user')->filter();

            if ($monitorUsers->isEmpty()) {
                continue;
            }

            $checkpoints = $this->leaderboardService->checkpointsFor($session->workshop);
            [$surveyResponses, $checkAttempts] = $this->leaderboardService->loadDataForSession($session->id);
            $rows = $this->leaderboardService->rowsFor(
                $session->participants()->notRemoved()->get(),
                $checkpoints,
                $surveyResponses,
                $checkAttempts,
            );

            $summary = [
                'total' => $session->participants()->notRemoved()->count(),
                'completed' => $session->participants()->notRemoved()->completed()->count(),
                'at_risk' => collect($rows)->filter(fn (array $row) => in_array('failed', $row['statuses'], true))->count(),
            ];

            foreach ($monitorUsers as $user) {
                $user->notify(new WorkshopDailyParticipationDigest($session, $summary));
                $count++;
            }
        }

        return $count;
    }
}
