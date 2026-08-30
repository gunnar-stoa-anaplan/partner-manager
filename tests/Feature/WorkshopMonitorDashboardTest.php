<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopPage;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionMonitor;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopMonitorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('workshops');
    }

    public function test_a_session_monitor_without_management_permission_can_view_the_dashboard(): void
    {
        $monitorUser = User::factory()->pam()->create();
        $session = WorkshopSession::factory()->create();
        WorkshopSessionMonitor::factory()->create([
            'workshop_session_id' => $session->id,
            'user_id' => $monitorUser->id,
        ]);

        $response = $this->actingAs($monitorUser)->get(route('workshops.monitor.show', $session));

        $response->assertOk();
    }

    public function test_a_user_who_is_not_a_monitor_and_lacks_management_permission_is_forbidden(): void
    {
        $outsider = User::factory()->pam()->create();
        $session = WorkshopSession::factory()->create();

        $response = $this->actingAs($outsider)->get(route('workshops.monitor.show', $session));

        $response->assertForbidden();
    }

    public function test_a_session_monitor_can_view_a_facilitator_only_page_for_their_session(): void
    {
        $monitorUser = User::factory()->pam()->create();
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        WorkshopSessionMonitor::factory()->create([
            'workshop_session_id' => $session->id,
            'user_id' => $monitorUser->id,
        ]);
        $page = WorkshopPage::factory()->create([
            'workshop_id' => $workshop->id,
            'audience' => 'facilitator',
        ]);

        $response = $this->actingAs($monitorUser)->get(route('workshops.monitor.pages.show', [$session, $page]));

        $response->assertOk();
        $response->assertSee($page->title);
    }

    public function test_a_participant_facing_page_is_not_reachable_through_the_facilitator_route(): void
    {
        $monitorUser = User::factory()->pam()->create();
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        WorkshopSessionMonitor::factory()->create([
            'workshop_session_id' => $session->id,
            'user_id' => $monitorUser->id,
        ]);
        $page = WorkshopPage::factory()->create([
            'workshop_id' => $workshop->id,
            'audience' => 'participant',
        ]);

        $response = $this->actingAs($monitorUser)->get(route('workshops.monitor.pages.show', [$session, $page]));

        $response->assertNotFound();
    }

    public function test_a_facilitator_page_from_a_different_workshop_is_not_reachable(): void
    {
        $monitorUser = User::factory()->pam()->create();
        $session = WorkshopSession::factory()->create();
        WorkshopSessionMonitor::factory()->create([
            'workshop_session_id' => $session->id,
            'user_id' => $monitorUser->id,
        ]);
        $otherWorkshopPage = WorkshopPage::factory()->create(['audience' => 'facilitator']);

        $response = $this->actingAs($monitorUser)->get(route('workshops.monitor.pages.show', [$session, $otherWorkshopPage]));

        $response->assertNotFound();
    }

    public function test_a_user_with_management_permission_can_view_a_facilitator_page_without_being_a_monitor(): void
    {
        $coe = User::factory()->coe()->create();
        $workshop = Workshop::factory()->create();
        $session = WorkshopSession::factory()->create(['workshop_id' => $workshop->id]);
        $page = WorkshopPage::factory()->create([
            'workshop_id' => $workshop->id,
            'audience' => 'facilitator',
        ]);

        $response = $this->actingAs($coe)->get(route('workshops.monitor.pages.show', [$session, $page]));

        $response->assertOk();
    }
}
