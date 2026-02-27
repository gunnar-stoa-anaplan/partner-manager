<?php

namespace Tests\Unit;

use App\Enums\ZoomMeetingStatus;
use App\Jobs\SyncMeetingParticipantsJob;
use App\Models\EnablementEvent;
use App\Models\EnablementSession;
use App\Models\User;
use App\Models\WorkshopAttendance;
use App\Models\ZoomMeeting;
use App\Services\WorkshopMeetingService;
use App\Services\ZoomApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WorkshopMeetingServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkshopMeetingService $service;

    private ZoomApiService $zoomApi;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $this->zoomApi = new ZoomApiService(
            accountId: 'test-account-id',
            clientId: 'test-client-id',
            clientSecret: 'test-client-secret',
            webhookSecretToken: 'test-webhook-secret',
        );

        $this->service = new WorkshopMeetingService($this->zoomApi);

        config(['services.zoom.host_email' => 'host@example.com']);

        Cache::forget('zoom_s2s_access_token');
    }

    private function fakeZoomApi(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response(['access_token' => 'fake-token'], 200),
            'api.zoom.us/v2/users/*/meetings' => Http::response([
                'id' => '85000000001',
                'uuid' => 'abc-uuid-123',
                'join_url' => 'https://zoom.us/j/85000000001',
                'start_url' => 'https://zoom.us/s/85000000001?token=start_token',
                'password' => 'ws1234',
            ], 201),
            'api.zoom.us/v2/meetings/*' => Http::response([], 204),
        ]);
    }

    public function test_creates_zoom_meeting_for_session(): void
    {
        $this->fakeZoomApi();

        $event = EnablementEvent::factory()->create(['type' => 'workshop', 'created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(7),
            'end_at' => now()->addDays(7)->addHours(2),
            'created_by' => $this->admin->id,
        ]);

        $meeting = $this->service->createForSession($session, $this->admin);

        $this->assertInstanceOf(ZoomMeeting::class, $meeting);
        $this->assertEquals('85000000001', $meeting->zoom_meeting_id);
        $this->assertEquals('https://zoom.us/j/85000000001', $meeting->join_url);
        $this->assertEquals(ZoomMeetingStatus::Scheduled, $meeting->status);
        $this->assertEquals($session->id, $meeting->enablement_session_id);
        $this->assertEquals($this->admin->id, $meeting->created_by);

        // Session meeting_url should be updated
        $session->refresh();
        $this->assertEquals('https://zoom.us/j/85000000001', $session->meeting_url);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'users/host@example.com/meetings')
                && $request['type'] === 2
                && $request['settings']['waiting_room'] === true;
        });
    }

    public function test_syncs_meeting_with_updated_session(): void
    {
        $this->fakeZoomApi();

        $event = EnablementEvent::factory()->create(['type' => 'workshop', 'created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'topic' => 'Old Topic',
        ]);

        $session->update(['title_override' => 'New Workshop Title']);

        $this->service->syncWithSession($session);

        $meeting->refresh();
        $this->assertEquals('New Workshop Title', $meeting->topic);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_contains($request->url(), 'meetings/');
        });
    }

    public function test_cancels_zoom_meeting(): void
    {
        $this->fakeZoomApi();

        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'created_by' => $this->admin->id,
        ]);

        $this->service->cancelForSession($session);

        $meeting->refresh();
        $this->assertEquals(ZoomMeetingStatus::Cancelled, $meeting->status);

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE'
                && str_contains($request->url(), 'meetings/');
        });
    }

    public function test_cancel_does_nothing_without_zoom_meeting(): void
    {
        $this->fakeZoomApi();

        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $this->service->cancelForSession($session);

        Http::assertNothingSent();
    }

    public function test_calculates_duration_from_session_times(): void
    {
        $this->fakeZoomApi();

        $event = EnablementEvent::factory()->create(['type' => 'workshop', 'created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'start_at' => now()->addDays(7),
            'end_at' => now()->addDays(7)->addHours(3),
            'created_by' => $this->admin->id,
        ]);

        $this->service->createForSession($session, $this->admin);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'users/') && $request['duration'] === 180;
        });
    }

    public function test_handle_meeting_started_updates_status(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'zoom_meeting_id' => '85000000001',
            'created_by' => $this->admin->id,
        ]);

        $this->service->handleMeetingStarted('85000000001');

        $meeting->refresh();
        $this->assertEquals(ZoomMeetingStatus::Started, $meeting->status);
        $this->assertNotNull($meeting->started_at);
    }

    public function test_handle_meeting_ended_updates_status_and_dispatches_sync(): void
    {
        Queue::fake();

        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'zoom_meeting_id' => '85000000001',
            'status' => ZoomMeetingStatus::Started,
            'created_by' => $this->admin->id,
        ]);

        $this->service->handleMeetingEnded('85000000001');

        $meeting->refresh();
        $this->assertEquals(ZoomMeetingStatus::Ended, $meeting->status);
        $this->assertNotNull($meeting->ended_at);

        Queue::assertPushed(SyncMeetingParticipantsJob::class);
    }

    public function test_handle_participant_joined_creates_attendance(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->started()->create([
            'enablement_session_id' => $session->id,
            'zoom_meeting_id' => '85000000001',
            'created_by' => $this->admin->id,
        ]);

        $this->service->handleParticipantJoined('85000000001', [
            'user_id' => 'zoom-001',
            'user_name' => 'Alice',
            'email' => 'alice@example.com',
            'join_time' => now()->toIso8601String(),
        ]);

        $this->assertDatabaseHas('workshop_attendances', [
            'zoom_meeting_id' => $meeting->id,
            'participant_email' => 'alice@example.com',
            'participant_name' => 'Alice',
            'source' => 'webhook',
        ]);
    }

    public function test_handle_participant_left_updates_duration(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->started()->create([
            'enablement_session_id' => $session->id,
            'zoom_meeting_id' => '85000000001',
            'created_by' => $this->admin->id,
        ]);

        $joinTime = now()->subMinutes(30);
        WorkshopAttendance::factory()->create([
            'zoom_meeting_id' => $meeting->id,
            'participant_email' => 'alice@example.com',
            'join_time' => $joinTime,
            'leave_time' => null,
        ]);

        $this->service->handleParticipantLeft('85000000001', [
            'user_id' => 'zoom-001',
            'user_name' => 'Alice',
            'email' => 'alice@example.com',
            'leave_time' => now()->toIso8601String(),
        ]);

        $attendance = WorkshopAttendance::where('participant_email', 'alice@example.com')->first();
        $this->assertNotNull($attendance->leave_time);
        $this->assertGreaterThan(0, $attendance->duration_seconds);
    }

    public function test_sync_participants_from_zoom_report(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response(['access_token' => 'fake-token'], 200),
            'api.zoom.us/v2/report/meetings/*/participants*' => Http::response([
                'participants' => [
                    [
                        'id' => 'zoom-001',
                        'name' => 'Alice Smith',
                        'user_email' => 'alice@example.com',
                        'join_time' => '2026-02-26T10:00:00Z',
                        'leave_time' => '2026-02-26T11:30:00Z',
                        'duration' => 5400,
                        'attentiveness_score' => 95,
                    ],
                    [
                        'id' => 'zoom-002',
                        'name' => 'Bob Jones',
                        'user_email' => 'bob@example.com',
                        'join_time' => '2026-02-26T10:05:00Z',
                        'leave_time' => '2026-02-26T11:25:00Z',
                        'duration' => 4800,
                        'attentiveness_score' => 82,
                    ],
                ],
            ], 200),
        ]);

        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->ended()->create([
            'enablement_session_id' => $session->id,
            'zoom_meeting_id' => '85000000001',
            'created_by' => $this->admin->id,
        ]);

        $this->service->syncParticipants($meeting);

        $this->assertEquals(2, WorkshopAttendance::where('zoom_meeting_id', $meeting->id)->count());
        $this->assertDatabaseHas('workshop_attendances', [
            'participant_email' => 'alice@example.com',
            'attentiveness_score' => 95,
            'source' => 'report',
        ]);

        $meeting->refresh();
        $this->assertEquals(2, $meeting->participants_count);
    }

    public function test_generate_sdk_signature_returns_jwt(): void
    {
        config([
            'services.zoom.sdk_key' => 'test-sdk-key',
            'services.zoom.sdk_secret' => 'test-sdk-secret',
        ]);

        $signature = $this->zoomApi->generateSdkSignature('85000000001', 1);

        $this->assertNotEmpty($signature);
        $parts = explode('.', $signature);
        $this->assertCount(3, $parts);

        // Verify header
        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
        $this->assertEquals('HS256', $header['alg']);

        // Verify payload
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $this->assertEquals('test-sdk-key', $payload['sdkKey']);
        $this->assertEquals('85000000001', $payload['mn']);
        $this->assertEquals(1, $payload['role']);
    }
}
