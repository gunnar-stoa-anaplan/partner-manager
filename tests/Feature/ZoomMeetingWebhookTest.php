<?php

namespace Tests\Feature;

use App\Enums\ZoomMeetingStatus;
use App\Jobs\SyncMeetingParticipantsJob;
use App\Models\EnablementEvent;
use App\Models\EnablementSession;
use App\Models\User;
use App\Models\WorkshopAttendance;
use App\Models\ZoomMeeting;
use App\Services\ZoomApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ZoomMeetingWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookUrl = '/api/webhooks/zoom/chat';

    private string $secretToken = 'test-secret-token';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $this->app->singleton(ZoomApiService::class, function () {
            return new ZoomApiService(
                accountId: 'test-account',
                clientId: 'test-client',
                clientSecret: 'test-secret',
                webhookSecretToken: $this->secretToken,
            );
        });
    }

    private function signPayload(string $payload, string $timestamp): string
    {
        $message = "v0:{$timestamp}:{$payload}";
        $hash = hash_hmac('sha256', $message, $this->secretToken);

        return "v0={$hash}";
    }

    private function sendSignedWebhook(array $payload): \Illuminate\Testing\TestResponse
    {
        $json = json_encode($payload);
        $timestamp = (string) time();
        $signature = $this->signPayload($json, $timestamp);

        return $this->call('POST', $this->webhookUrl, [], [], [], [
            'HTTP_X_ZM_SIGNATURE' => $signature,
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
            'CONTENT_TYPE' => 'application/json',
        ], $json);
    }

    private function createMeetingWithSession(array $meetingOverrides = []): ZoomMeeting
    {
        $event = EnablementEvent::factory()->create(['type' => 'workshop', 'created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        return ZoomMeeting::factory()->create(array_merge([
            'enablement_session_id' => $session->id,
            'created_by' => $this->admin->id,
            'zoom_meeting_id' => '85000000001',
        ], $meetingOverrides));
    }

    // ─── meeting.started ────────────────────────────────────────

    public function test_meeting_started_updates_status(): void
    {
        $meeting = $this->createMeetingWithSession();

        $response = $this->sendSignedWebhook([
            'event' => 'meeting.started',
            'payload' => ['object' => ['id' => '85000000001']],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'processed']);

        $meeting->refresh();
        $this->assertEquals(ZoomMeetingStatus::Started, $meeting->status);
        $this->assertNotNull($meeting->started_at);
    }

    public function test_meeting_started_ignores_untracked_meeting(): void
    {
        $response = $this->sendSignedWebhook([
            'event' => 'meeting.started',
            'payload' => ['object' => ['id' => '99999999999']],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'processed']);
    }

    // ─── meeting.ended ──────────────────────────────────────────

    public function test_meeting_ended_updates_status_and_dispatches_sync(): void
    {
        Queue::fake();

        $meeting = $this->createMeetingWithSession(['status' => ZoomMeetingStatus::Started]);

        $response = $this->sendSignedWebhook([
            'event' => 'meeting.ended',
            'payload' => ['object' => ['id' => '85000000001']],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'processed']);

        $meeting->refresh();
        $this->assertEquals(ZoomMeetingStatus::Ended, $meeting->status);
        $this->assertNotNull($meeting->ended_at);

        Queue::assertPushed(SyncMeetingParticipantsJob::class, function ($job) {
            return $job->delay !== null;
        });
    }

    public function test_meeting_ended_ignores_untracked_meeting(): void
    {
        Queue::fake();

        $response = $this->sendSignedWebhook([
            'event' => 'meeting.ended',
            'payload' => ['object' => ['id' => '99999999999']],
        ]);

        $response->assertOk();
        Queue::assertNotPushed(SyncMeetingParticipantsJob::class);
    }

    // ─── meeting.participant_joined ─────────────────────────────

    public function test_participant_joined_creates_attendance_record(): void
    {
        $meeting = $this->createMeetingWithSession(['status' => ZoomMeetingStatus::Started]);

        $response = $this->sendSignedWebhook([
            'event' => 'meeting.participant_joined',
            'payload' => [
                'object' => [
                    'id' => '85000000001',
                    'participant' => [
                        'user_id' => 'zoom-user-001',
                        'user_name' => 'Jane Doe',
                        'email' => 'jane@example.com',
                        'join_time' => now()->toIso8601String(),
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'processed']);

        $this->assertDatabaseHas('workshop_attendances', [
            'zoom_meeting_id' => $meeting->id,
            'participant_email' => 'jane@example.com',
            'participant_name' => 'Jane Doe',
            'source' => 'webhook',
        ]);
    }

    public function test_participant_joined_ignores_untracked_meeting(): void
    {
        $response = $this->sendSignedWebhook([
            'event' => 'meeting.participant_joined',
            'payload' => [
                'object' => [
                    'id' => '99999999999',
                    'participant' => [
                        'user_id' => 'zoom-user-001',
                        'user_name' => 'Jane Doe',
                        'email' => 'jane@example.com',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('workshop_attendances', 0);
    }

    // ─── meeting.participant_left ───────────────────────────────

    public function test_participant_left_updates_attendance_record(): void
    {
        $meeting = $this->createMeetingWithSession(['status' => ZoomMeetingStatus::Started]);

        $joinTime = now()->subMinutes(45);
        WorkshopAttendance::factory()->create([
            'zoom_meeting_id' => $meeting->id,
            'participant_email' => 'jane@example.com',
            'join_time' => $joinTime,
            'leave_time' => null,
        ]);

        $leaveTime = now();
        $response = $this->sendSignedWebhook([
            'event' => 'meeting.participant_left',
            'payload' => [
                'object' => [
                    'id' => '85000000001',
                    'participant' => [
                        'user_id' => 'zoom-user-001',
                        'user_name' => 'Jane Doe',
                        'email' => 'jane@example.com',
                        'leave_time' => $leaveTime->toIso8601String(),
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'processed']);

        $attendance = WorkshopAttendance::where('participant_email', 'jane@example.com')->first();
        $this->assertNotNull($attendance->leave_time);
        $this->assertGreaterThan(0, $attendance->duration_seconds);
    }

    public function test_participant_left_without_join_record_is_ignored(): void
    {
        $this->createMeetingWithSession(['status' => ZoomMeetingStatus::Started]);

        $response = $this->sendSignedWebhook([
            'event' => 'meeting.participant_left',
            'payload' => [
                'object' => [
                    'id' => '85000000001',
                    'participant' => [
                        'user_id' => 'zoom-user-unknown',
                        'user_name' => 'Ghost User',
                        'email' => 'ghost@example.com',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('workshop_attendances', 0);
    }

    // ─── Unknown events ─────────────────────────────────────────

    public function test_unknown_event_type_is_ignored(): void
    {
        $response = $this->sendSignedWebhook([
            'event' => 'recording.completed',
            'payload' => ['object' => ['id' => '85000000001']],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'ignored']);
    }

    // ─── Incomplete payloads ────────────────────────────────────

    public function test_meeting_started_without_id_returns_incomplete(): void
    {
        $response = $this->sendSignedWebhook([
            'event' => 'meeting.started',
            'payload' => ['object' => []],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'incomplete_payload']);
    }

    public function test_participant_joined_without_participant_returns_incomplete(): void
    {
        $response = $this->sendSignedWebhook([
            'event' => 'meeting.participant_joined',
            'payload' => ['object' => ['id' => '85000000001']],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 'incomplete_payload']);
    }
}
