<?php

namespace Tests\Feature;

use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\StepProgress;
use App\Models\User;
use App\Services\ActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivationPathAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ActivationPath $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement',
            'is_active' => true,
        ]);
    }

    // ─── View Assignments ──────────────────────────────────────

    public function test_admin_can_view_assignments_page(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('admin.activation.paths.assignments', $this->path)
        );

        $response->assertOk();
        $response->assertViewIs('admin.activation.assignments');
        $response->assertViewHas('path');
        $response->assertViewHas('availableUsers');
    }

    public function test_available_users_excludes_already_assigned(): void
    {
        $assigned = User::factory()->create();
        $unassigned = User::factory()->create();

        $this->path->users()->attach($assigned->id, ['assigned_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.activation.paths.assignments', $this->path)
        );

        $availableUsers = $response->viewData('availableUsers');
        $this->assertFalse($availableUsers->contains('id', $assigned->id));
        $this->assertTrue($availableUsers->contains('id', $unassigned->id));
    }

    // ─── Assign Users ──────────────────────────────────────────

    public function test_admin_can_assign_users_to_path(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($this->admin)->post(
            route('admin.activation.paths.assign-users', $this->path),
            ['user_ids' => [$user1->id, $user2->id]]
        );

        $response->assertRedirect(route('admin.activation.paths.assignments', $this->path));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('activation_path_user', [
            'activation_path_id' => $this->path->id,
            'user_id' => $user1->id,
            'assigned_by' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('activation_path_user', [
            'activation_path_id' => $this->path->id,
            'user_id' => $user2->id,
            'assigned_by' => $this->admin->id,
        ]);
    }

    public function test_assign_validates_user_ids_required(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.activation.paths.assign-users', $this->path),
            ['user_ids' => []]
        );

        $response->assertSessionHasErrors('user_ids');
    }

    public function test_assign_validates_user_ids_exist(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.activation.paths.assign-users', $this->path),
            ['user_ids' => [99999]]
        );

        $response->assertSessionHasErrors('user_ids.0');
    }

    // ─── Unassign User ─────────────────────────────────────────

    public function test_admin_can_unassign_user_from_path(): void
    {
        $user = User::factory()->create();
        $this->path->users()->attach($user->id, ['assigned_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(
            route('admin.activation.paths.unassign-user', [$this->path, $user])
        );

        $response->assertRedirect(route('admin.activation.paths.assignments', $this->path));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('activation_path_user', [
            'activation_path_id' => $this->path->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_unassign_cleans_up_step_progress(): void
    {
        $user = User::factory()->create();
        $step = ActivationStep::factory()->create([
            'activation_path_id' => $this->path->id,
            'is_required' => true,
        ]);

        $this->path->users()->attach($user->id, ['assigned_by' => $this->admin->id]);

        StepProgress::create([
            'activation_step_id' => $step->id,
            'user_id' => $user->id,
            'current_value' => 1,
            'target_value' => 1,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $this->actingAs($this->admin)->delete(
            route('admin.activation.paths.unassign-user', [$this->path, $user])
        );

        $this->assertDatabaseMissing('step_progress', [
            'activation_step_id' => $step->id,
            'user_id' => $user->id,
        ]);
    }

    // ─── User Progress View ────────────────────────────────────

    public function test_admin_can_view_user_progress(): void
    {
        $user = User::factory()->create();
        $this->path->users()->attach($user->id, ['assigned_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.activation.user-progress', $user)
        );

        $response->assertOk();
        $response->assertViewIs('admin.activation.user-progress');
        $response->assertViewHas('user');
        $response->assertViewHas('paths');
    }

    // ─── PSM Cannot Access Assignment Pages ────────────────────

    public function test_psm_cannot_view_assignments(): void
    {
        $psm = User::factory()->psm()->create();

        $response = $this->actingAs($psm)->get(
            route('admin.activation.paths.assignments', $this->path)
        );

        $response->assertForbidden();
    }

    public function test_psm_cannot_assign_users(): void
    {
        $psm = User::factory()->psm()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($psm)->post(
            route('admin.activation.paths.assign-users', $this->path),
            ['user_ids' => [$user->id]]
        );

        $response->assertForbidden();
    }

    // ─── Service: assignPath & bulkAssignPath ──────────────────

    public function test_assign_path_service_creates_pivot(): void
    {
        $user = User::factory()->create();
        $service = app(ActivationService::class);

        $service->assignPath($this->path, $user, $this->admin);

        $this->assertDatabaseHas('activation_path_user', [
            'activation_path_id' => $this->path->id,
            'user_id' => $user->id,
            'assigned_by' => $this->admin->id,
        ]);
    }

    public function test_bulk_assign_path_service_creates_multiple_pivots(): void
    {
        $users = User::factory()->count(3)->create();
        $service = app(ActivationService::class);

        $service->bulkAssignPath($this->path, $users, $this->admin);

        foreach ($users as $user) {
            $this->assertDatabaseHas('activation_path_user', [
                'activation_path_id' => $this->path->id,
                'user_id' => $user->id,
                'assigned_by' => $this->admin->id,
            ]);
        }
    }

    public function test_assign_path_is_idempotent(): void
    {
        $user = User::factory()->create();
        $service = app(ActivationService::class);

        $service->assignPath($this->path, $user, $this->admin);
        $service->assignPath($this->path, $user, $this->admin);

        $this->assertDatabaseCount('activation_path_user', 1);
    }
}
