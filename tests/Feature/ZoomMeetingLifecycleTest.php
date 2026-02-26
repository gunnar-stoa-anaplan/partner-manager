<?php

namespace Tests\Feature;

use App\Enums\ZoomMeetingStatus;
use App\Jobs\CancelZoomMeetingJob;
use App\Jobs\CreateZoomMeetingJob;
use App\Models\EnablementEvent;
use App\Models\EnablementSession;
use App\Models\User;
use App\Models\WorkshopAttendance;
use App\Models\ZoomMeeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ZoomMeetingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_creating_workshop_session_dispatches_zoom_meeting_job(): void
    {
        Bus::fake();

        $event = EnablementEvent::factory()->create(['type' => 'workshop', 'created_by' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->post(route('enablement-events.sessions.store', $event->id), [
                'start_at' => now()->addDays(7)->toDateTimeString(),
                'end_at' => now()->addDays(7)->addHours(2)->toDateTimeString(),
                'timezone' => 'UTC',
                'status' => 'scheduled',
            ])
            ->assertRedirect();

        Bus::assertDispatched(CreateZoomMeetingJob::class);
    }

    public function test_creating_non_workshop_session_does_not_dispatch_zoom_job(): void
    {
        Bus::fake();

        $event = EnablementEvent::factory()->create(['type' => 'webinar', 'created_by' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->post(route('enablement-events.sessions.store', $event->id), [
                'start_at' => now()->addDays(7)->toDateTimeString(),
                'end_at' => now()->addDays(7)->addHours(2)->toDateTimeString(),
                'timezone' => 'UTC',
                'status' => 'scheduled',
            ])
            ->assertRedirect();

        Bus::assertNotDispatched(CreateZoomMeetingJob::class);
    }

    public function test_deleting_session_with_zoom_meeting_dispatches_cancel_job(): void
    {
        Bus::fake();

        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('enablement-sessions.destroy', $session->id))
            ->assertRedirect();

        Bus::assertDispatched(CancelZoomMeetingJob::class);
    }

    public function test_deleting_session_without_zoom_meeting_does_not_dispatch_cancel_job(): void
    {
        Bus::fake();

        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('enablement-sessions.destroy', $session->id))
            ->assertRedirect();

        Bus::assertNotDispatched(CancelZoomMeetingJob::class);
    }

    public function test_zoom_meeting_model_relationships(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals($session->id, $meeting->enablementSession->id);
        $this->assertEquals($this->admin->id, $meeting->creator->id);
        $this->assertNotNull($session->zoomMeeting);
        $this->assertEquals($meeting->id, $session->zoomMeeting->id);
    }

    public function test_zoom_meeting_status_helpers(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $sessions = EnablementSession::factory()->count(3)->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        $scheduled = ZoomMeeting::factory()->create(['enablement_session_id' => $sessions[0]->id, 'created_by' => $this->admin->id]);
        $started = ZoomMeeting::factory()->started()->create(['enablement_session_id' => $sessions[1]->id, 'created_by' => $this->admin->id]);
        $ended = ZoomMeeting::factory()->ended()->create(['enablement_session_id' => $sessions[2]->id, 'created_by' => $this->admin->id]);

        $this->assertFalse($scheduled->isLive());
        $this->assertFalse($scheduled->hasEnded());

        $this->assertTrue($started->isLive());
        $this->assertFalse($started->hasEnded());

        $this->assertFalse($ended->isLive());
        $this->assertTrue($ended->hasEnded());
    }

    public function test_zoom_meeting_scopes(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $sessions = EnablementSession::factory()->count(4)->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);

        ZoomMeeting::factory()->create(['status' => ZoomMeetingStatus::Scheduled, 'enablement_session_id' => $sessions[0]->id, 'created_by' => $this->admin->id]);
        ZoomMeeting::factory()->started()->create(['enablement_session_id' => $sessions[1]->id, 'created_by' => $this->admin->id]);
        ZoomMeeting::factory()->ended()->create(['enablement_session_id' => $sessions[2]->id, 'created_by' => $this->admin->id]);
        ZoomMeeting::factory()->cancelled()->create(['enablement_session_id' => $sessions[3]->id, 'created_by' => $this->admin->id]);

        $this->assertEquals(1, ZoomMeeting::scheduled()->count());
        $this->assertEquals(2, ZoomMeeting::active()->count());
    }

    public function test_workshop_attendance_relationships(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'created_by' => $this->admin->id,
        ]);
        $attendance = WorkshopAttendance::factory()->create([
            'zoom_meeting_id' => $meeting->id,
        ]);

        $this->assertEquals($meeting->id, $attendance->zoomMeeting->id);
        $this->assertCount(1, $meeting->attendances);
    }

    public function test_zoom_meeting_casts_status_to_enum(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $meeting = ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'created_by' => $this->admin->id,
        ]);

        $this->assertInstanceOf(ZoomMeetingStatus::class, $meeting->status);
        $this->assertEquals(ZoomMeetingStatus::Scheduled, $meeting->status);
    }

    public function test_zoom_meeting_casts_settings_to_array(): void
    {
        $event = EnablementEvent::factory()->create(['created_by' => $this->admin->id]);
        $session = EnablementSession::factory()->create([
            'enablement_event_id' => $event->id,
            'created_by' => $this->admin->id,
        ]);
        $settings = ['waiting_room' => true, 'mute_upon_entry' => true];
        $meeting = ZoomMeeting::factory()->create([
            'enablement_session_id' => $session->id,
            'created_by' => $this->admin->id,
            'settings' => $settings,
        ]);

        $meeting->refresh();
        $this->assertIsArray($meeting->settings);
        $this->assertTrue($meeting->settings['waiting_room']);
    }
}
