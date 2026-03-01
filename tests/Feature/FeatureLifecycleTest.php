<?php

namespace Tests\Feature;

use App\Enums\TrainingMode;
use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\Module;
use App\Models\StepProgress;
use App\Models\User;
use App\Services\ActivationService;
use App\Services\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class FeatureLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** User without a role — no role permission filtering */
    private User $user;

    private ModuleRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create(); // no role = bypasses role permission check

        $this->registry = app(ModuleRegistry::class);
    }

    // ─── Hidden → Locked → Unlocked lifecycle ──────────────────

    public function test_inactive_feature_returns_hidden_state(): void
    {
        $this->registry->register('test_feature', $this->moduleDefinition());
        Feature::for(null)->deactivate('test_feature');

        $this->assertEquals('hidden', $this->registry->getFeatureState('test_feature', $this->user));
    }

    public function test_active_feature_with_training_required_returns_locked_for_unassigned_user(): void
    {
        $this->registerWithTraining('test_feature', 'required');

        ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $this->assertEquals('locked', $this->registry->getFeatureState('test_feature', $this->user));
    }

    public function test_user_completes_training_and_feature_becomes_unlocked(): void
    {
        $this->registerWithTraining('test_feature', 'required');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $path->users()->attach($this->user->id, [
            'assigned_by' => $this->admin->id,
            'completed_at' => now(),
        ]);

        $this->assertEquals('unlocked', $this->registry->getFeatureState('test_feature', $this->user));
    }

    // ─── Training mode: recommended → nudge ─────────────────────

    public function test_recommended_training_mode_returns_nudge_state(): void
    {
        $this->registerWithTraining('test_feature', 'recommended');

        $this->assertEquals('nudge', $this->registry->getFeatureState('test_feature', $this->user));
    }

    public function test_nudge_state_allows_access_via_is_enabled(): void
    {
        $this->registerWithTraining('test_feature', 'recommended');

        $this->assertTrue($this->registry->isEnabled('test_feature', $this->user));
    }

    // ─── Training mode: none → instant access ──────────────────

    public function test_no_training_mode_returns_unlocked_immediately(): void
    {
        $this->registerWithTraining('test_feature', 'none');

        $this->assertEquals('unlocked', $this->registry->getFeatureState('test_feature', $this->user));
    }

    // ─── Early access bypass ────────────────────────────────────

    public function test_bypassed_user_gets_unlocked_state(): void
    {
        $this->registerWithTraining('test_feature', 'required');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $path->users()->attach($this->user->id, [
            'assigned_by' => $this->admin->id,
            'training_bypassed_at' => now(),
        ]);

        $this->assertEquals('unlocked', $this->registry->getFeatureState('test_feature', $this->user));
    }

    // ─── Admin always unlocked ──────────────────────────────────

    public function test_admin_always_sees_unlocked_state(): void
    {
        $this->registerWithTraining('test_feature', 'required');

        ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $this->assertEquals('unlocked', $this->registry->getFeatureState('test_feature', $this->admin));
    }

    // ─── Navigation sections include locked items ───────────────

    public function test_navigation_sections_include_locked_items_with_state(): void
    {
        $this->registerWithTraining('test_feature', 'required');

        ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $sections = $this->registry->getNavigationSections($this->user);

        $testSection = collect($sections)->firstWhere('feature', 'test_feature');

        $this->assertNotNull($testSection);
        $this->assertEquals('locked', $testSection['state']);
    }

    public function test_accessible_sections_excludes_locked_items(): void
    {
        $this->registerWithTraining('test_feature', 'required');

        ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $sections = $this->registry->getAccessibleSections($this->user);

        $testSection = collect($sections)->firstWhere('feature', 'test_feature');

        $this->assertNull($testSection);
    }

    // ─── Training progress tracking ─────────────────────────────

    public function test_training_progress_returned_for_locked_sections(): void
    {
        $this->registerWithTraining('test_feature', 'required');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $step1 = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'order' => 1,
        ]);

        ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'order' => 2,
        ]);

        $path->users()->attach($this->user->id, ['assigned_by' => $this->admin->id]);

        StepProgress::factory()->create([
            'activation_step_id' => $step1->id,
            'user_id' => $this->user->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $sections = $this->registry->getNavigationSections($this->user);
        $testSection = collect($sections)->firstWhere('feature', 'test_feature');

        $this->assertNotNull($testSection);
        $this->assertNotNull($testSection['training_progress']);
        $this->assertEquals(1, $testSection['training_progress']['completed']);
        $this->assertEquals(2, $testSection['training_progress']['total']);
    }

    // ─── Graduated section unlock ───────────────────────────────

    public function test_graduated_unlock_unlocks_specific_section(): void
    {
        $this->registry->register('test_feature', [
            'name' => 'Test Feature',
            'description' => 'Test',
            'icon' => 'star',
            'group' => 'test',
            'enabled' => true,
            'training_mode' => 'required',
            'sections' => [
                'basics' => [
                    'label' => 'Basics',
                    'icon' => 'list',
                    'items' => [['label' => 'List', 'route' => 'welcome']],
                ],
                'advanced' => [
                    'label' => 'Advanced',
                    'icon' => 'settings',
                    'items' => [['label' => 'Settings', 'route' => 'welcome']],
                ],
            ],
        ]);

        Feature::for(null)->activate('test_feature');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'unlocks_sections' => ['basics'],
        ]);

        $path->users()->attach($this->user->id, ['assigned_by' => $this->admin->id]);

        StepProgress::factory()->create([
            'activation_step_id' => $step->id,
            'user_id' => $this->user->id,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $this->assertEquals('unlocked', $this->registry->getSectionState('test_feature', 'basics', $this->user));
        $this->assertEquals('locked', $this->registry->getSectionState('test_feature', 'advanced', $this->user));
    }

    // ─── Middleware redirect for locked ─────────────────────────

    public function test_locked_feature_redirects_to_training(): void
    {
        $this->registerWithTraining('enablement', 'required');

        $path = ActivationPath::factory()->create([
            'feature_slug' => 'enablement',
            'is_active' => true,
        ]);

        $path->users()->attach($this->user->id, ['assigned_by' => $this->admin->id]);

        // influence_map route is gated by module:enablement.field only (no admin middleware)
        $response = $this->actingAs($this->user)->get(route('influence_map'));

        $response->assertRedirect(route('training.index', ['feature' => 'enablement']));
    }

    // ─── Unlock celebration flash ───────────────────────────────

    public function test_completing_training_flashes_unlock_celebration(): void
    {
        $path = ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $step = ActivationStep::factory()->create([
            'activation_path_id' => $path->id,
            'is_required' => true,
            'criteria' => ['target' => 1],
        ]);

        $path->users()->attach($this->user->id, ['assigned_by' => $this->admin->id]);

        $service = app(ActivationService::class);

        $this->actingAs($this->user);
        session()->start();

        $service->recordProgress($step, 1, $this->user);

        $this->assertEquals('test_feature', session('feature_unlocked'));
    }

    // ─── Admin training mode update ─────────────────────────────

    public function test_admin_can_update_training_mode(): void
    {
        Module::factory()->create(['slug' => 'test_feature', 'training_mode' => 'none']);

        $response = $this->actingAs($this->admin)->put(route('admin.activation.training-mode'), [
            'slug' => 'test_feature',
            'training_mode' => 'required',
        ]);

        $response->assertRedirect();

        $module = Module::where('slug', 'test_feature')->first();
        $this->assertEquals(TrainingMode::Required, $module->training_mode);
    }

    // ─── Admin early access bypass ──────────────────────────────

    public function test_admin_can_grant_and_revoke_early_access(): void
    {
        $path = ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $path->users()->attach($this->user->id, ['assigned_by' => $this->admin->id]);

        // Grant bypass
        $response = $this->actingAs($this->admin)
            ->post(route('admin.activation.paths.bypass-training', [$path, $this->user]));

        $response->assertRedirect();
        $this->assertNotNull(
            $path->users()->where('user_id', $this->user->id)->first()->pivot->training_bypassed_at
        );

        // Revoke bypass
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.activation.paths.revoke-bypass', [$path, $this->user]));

        $response->assertRedirect();
        $this->assertNull(
            $path->users()->where('user_id', $this->user->id)->first()->pivot->training_bypassed_at
        );
    }

    // ─── Feature preview page ────────────────────────────────────

    public function test_feature_preview_page_renders_for_locked_feature(): void
    {
        $module = Module::factory()->create([
            'slug' => 'test_feature',
            'training_mode' => 'required',
        ]);

        $this->registry->register('test_feature', $module->toRegistryArray());
        Feature::for(null)->activate('test_feature');

        ActivationPath::factory()->create([
            'feature_slug' => 'test_feature',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('features.preview', $module->slug));

        $response->assertOk();
        $response->assertSee($module->name);
    }

    // ─── Helpers ────────────────────────────────────────────────

    private function moduleDefinition(string $trainingMode = 'none'): array
    {
        return [
            'name' => 'Test Feature',
            'description' => 'Test module for lifecycle tests',
            'icon' => 'star',
            'group' => 'test',
            'enabled' => true,
            'training_mode' => $trainingMode,
            'sections' => [
                'dashboard' => [
                    'label' => 'Dashboard',
                    'icon' => 'home',
                    'items' => [['label' => 'Overview', 'route' => 'welcome']],
                ],
            ],
        ];
    }

    private function registerWithTraining(string $slug, string $mode): void
    {
        $this->registry->register($slug, $this->moduleDefinition($mode));
        Feature::for(null)->activate($slug);
    }
}
