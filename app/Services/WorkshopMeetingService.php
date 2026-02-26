<?php

namespace App\Services;

use App\Enums\ZoomMeetingStatus;
use App\Jobs\SyncMeetingParticipantsJob;
use App\Models\EnablementSession;
use App\Models\User;
use App\Models\WorkshopAttendance;
use App\Models\ZoomMeeting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WorkshopMeetingService
{
    public function __construct(
        private ZoomApiService $zoomApi,
    ) {}

    /**
     * Create a Zoom meeting linked to an enablement session.
     */
    public function createForSession(EnablementSession $session, User $creator): ZoomMeeting
    {
        $payload = $this->buildMeetingPayload($session);
        $hostEmail = config('services.zoom.host_email');

        $zoomData = $this->zoomApi->createMeeting($hostEmail, $payload);

        $meeting = ZoomMeeting::create([
            'enablement_session_id' => $session->id,
            'zoom_meeting_id' => $zoomData['id'],
            'zoom_uuid' => $zoomData['uuid'],
            'host_email' => $hostEmail,
            'join_url' => $zoomData['join_url'],
            'start_url' => $zoomData['start_url'],
            'password' => $zoomData['password'],
            'topic' => $payload['topic'],
            'agenda' => $payload['agenda'] ?? null,
            'duration_minutes' => $payload['duration'],
            'timezone' => $payload['timezone'],
            'status' => ZoomMeetingStatus::Scheduled,
            'settings' => $payload['settings'] ?? null,
            'created_by' => $creator->id,
        ]);

        // Update the session's meeting_url with the Zoom join URL
        $session->update(['meeting_url' => $zoomData['join_url']]);

        Log::info('Zoom meeting created for workshop session', [
            'session_id' => $session->id,
            'zoom_meeting_id' => $zoomData['id'],
        ]);

        return $meeting;
    }

    /**
     * Sync meeting details when session time/title changes.
     */
    public function syncWithSession(EnablementSession $session): void
    {
        $meeting = $session->zoomMeeting;
        if (! $meeting) {
            return;
        }

        $payload = $this->buildMeetingPayload($session);

        $updated = $this->zoomApi->updateMeeting($meeting->zoom_meeting_id, $payload);

        if ($updated) {
            $meeting->update([
                'topic' => $payload['topic'],
                'agenda' => $payload['agenda'] ?? null,
                'duration_minutes' => $payload['duration'],
                'timezone' => $payload['timezone'],
            ]);
        }
    }

    /**
     * Cancel the Zoom meeting when a session is cancelled or deleted.
     */
    public function cancelForSession(EnablementSession $session): void
    {
        $meeting = $session->zoomMeeting;
        if (! $meeting) {
            return;
        }

        $deleted = $this->zoomApi->deleteMeeting($meeting->zoom_meeting_id);

        if ($deleted) {
            $meeting->update(['status' => ZoomMeetingStatus::Cancelled]);
        }
    }

    // ─── Webhook Event Handlers ────────────────────────────────

    /**
     * Handle meeting.started webhook event.
     */
    public function handleMeetingStarted(string $zoomMeetingId): void
    {
        $meeting = ZoomMeeting::where('zoom_meeting_id', $zoomMeetingId)->first();
        if (! $meeting) {
            Log::debug('Zoom meeting.started for untracked meeting', ['zoom_meeting_id' => $zoomMeetingId]);

            return;
        }

        $meeting->update([
            'status' => ZoomMeetingStatus::Started,
            'started_at' => now(),
        ]);

        Log::info('Zoom meeting started', [
            'zoom_meeting_id' => $zoomMeetingId,
            'session_id' => $meeting->enablement_session_id,
        ]);
    }

    /**
     * Handle meeting.ended webhook event.
     * Dispatches a delayed job to fetch participant report from Zoom API.
     */
    public function handleMeetingEnded(string $zoomMeetingId): void
    {
        $meeting = ZoomMeeting::where('zoom_meeting_id', $zoomMeetingId)->first();
        if (! $meeting) {
            Log::debug('Zoom meeting.ended for untracked meeting', ['zoom_meeting_id' => $zoomMeetingId]);

            return;
        }

        $meeting->update([
            'status' => ZoomMeetingStatus::Ended,
            'ended_at' => now(),
        ]);

        // Zoom participant reports are available ~15 minutes after meeting ends
        SyncMeetingParticipantsJob::dispatch($meeting->id)->delay(now()->addMinutes(15));

        Log::info('Zoom meeting ended', [
            'zoom_meeting_id' => $zoomMeetingId,
            'session_id' => $meeting->enablement_session_id,
        ]);
    }

    /**
     * Handle meeting.participant_joined webhook event.
     *
     * @param  array{user_id?: string, user_name?: string, email?: string, join_time?: string}  $participant
     */
    public function handleParticipantJoined(string $zoomMeetingId, array $participant): void
    {
        $meeting = ZoomMeeting::where('zoom_meeting_id', $zoomMeetingId)->first();
        if (! $meeting) {
            return;
        }

        $email = $participant['email'] ?? null;
        $zoomUserId = $participant['user_id'] ?? null;

        WorkshopAttendance::updateOrCreate(
            [
                'zoom_meeting_id' => $meeting->id,
                'participant_email' => $email ?? $zoomUserId,
            ],
            [
                'participant_name' => $participant['user_name'] ?? 'Unknown',
                'zoom_user_id' => $zoomUserId,
                'join_time' => isset($participant['join_time']) ? Carbon::parse($participant['join_time']) : now(),
                'source' => 'webhook',
            ],
        );
    }

    /**
     * Handle meeting.participant_left webhook event.
     *
     * @param  array{user_id?: string, user_name?: string, email?: string, leave_time?: string}  $participant
     */
    public function handleParticipantLeft(string $zoomMeetingId, array $participant): void
    {
        $meeting = ZoomMeeting::where('zoom_meeting_id', $zoomMeetingId)->first();
        if (! $meeting) {
            return;
        }

        $email = $participant['email'] ?? null;
        $zoomUserId = $participant['user_id'] ?? null;

        $attendance = WorkshopAttendance::where('zoom_meeting_id', $meeting->id)
            ->where('participant_email', $email ?? $zoomUserId)
            ->first();

        if (! $attendance) {
            return;
        }

        $leaveTime = isset($participant['leave_time']) ? Carbon::parse($participant['leave_time']) : now();
        $durationSeconds = $attendance->join_time ? $attendance->join_time->diffInSeconds($leaveTime) : 0;

        $attendance->update([
            'leave_time' => $leaveTime,
            'duration_seconds' => $durationSeconds,
        ]);
    }

    /**
     * Sync participant data from Zoom's post-meeting report API.
     */
    public function syncParticipants(ZoomMeeting $meeting): void
    {
        $participants = $this->zoomApi->getMeetingParticipants($meeting->zoom_meeting_id);

        if (empty($participants)) {
            Log::info('No participant data from Zoom report', ['zoom_meeting_id' => $meeting->zoom_meeting_id]);

            return;
        }

        $totalParticipants = 0;

        foreach ($participants as $p) {
            $email = $p['user_email'] ?? $p['id'] ?? null;
            if (! $email) {
                continue;
            }

            WorkshopAttendance::updateOrCreate(
                [
                    'zoom_meeting_id' => $meeting->id,
                    'participant_email' => $email,
                ],
                [
                    'participant_name' => $p['name'] ?? 'Unknown',
                    'zoom_user_id' => $p['id'] ?? null,
                    'join_time' => isset($p['join_time']) ? Carbon::parse($p['join_time']) : null,
                    'leave_time' => isset($p['leave_time']) ? Carbon::parse($p['leave_time']) : null,
                    'duration_seconds' => $p['duration'] ?? null,
                    'attentiveness_score' => $p['attentiveness_score'] ?? null,
                    'source' => 'report',
                ],
            );

            $totalParticipants++;
        }

        $meeting->update(['participants_count' => $totalParticipants]);

        Log::info('Synced participants from Zoom report', [
            'zoom_meeting_id' => $meeting->zoom_meeting_id,
            'participants' => $totalParticipants,
        ]);
    }

    // ─── Meeting Payload Builder ─────────────────────────────────

    /**
     * Build the Zoom API meeting creation payload from session data.
     *
     * @return array{topic: string, type: int, start_time: string, duration: int, timezone: string, agenda: string|null, settings: array<string, mixed>}
     */
    private function buildMeetingPayload(EnablementSession $session): array
    {
        $session->loadMissing('event');

        $durationMinutes = 60;
        if ($session->start_at && $session->end_at) {
            $durationMinutes = (int) $session->start_at->diffInMinutes($session->end_at);
        } elseif ($session->event?->duration_hours) {
            $durationMinutes = (int) ($session->event->duration_hours * 60);
        }

        return [
            'topic' => $session->display_title,
            'type' => 2, // Scheduled meeting
            'start_time' => $session->start_at->toIso8601String(),
            'duration' => $durationMinutes,
            'timezone' => $session->timezone ?? 'UTC',
            'agenda' => $session->event?->description,
            'settings' => [
                'host_video' => true,
                'participant_video' => false,
                'join_before_host' => false,
                'mute_upon_entry' => true,
                'waiting_room' => true,
                'auto_recording' => 'cloud',
                'meeting_authentication' => false,
            ],
        ];
    }
}
