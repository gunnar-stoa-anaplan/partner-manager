<?php

namespace Tests\Feature;

use App\Models\ContentAsset;
use App\Models\Journey;
use App\Models\JourneyAssignment;
use App\Models\User;
use App\Services\ModuleRegistry;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class JourneyOwnerRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $owner;

    private User $psm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ModuleSeeder::class);
        $this->seed(RoleSeeder::class);

        Feature::for(null)->activate('journeys');

        $this->admin = User::factory()->admin()->create();
        $this->owner = User::factory()->journeyOwner()->create();
        $this->psm = User::factory()->psm()->create();
    }

    public function test_journey_owner_role_exists_with_minimal_permissions(): void
    {
        $role = $this->owner->role;

        $this->assertSame('journey_owner', $role->slug);
        $this->assertFalse((bool) $role->bypass_permissions);
        $this->assertSame('journeys.my', $role->landing_route);
        $this->assertTrue($this->owner->hasModulePermission('journeys.my_journeys'));
        $this->assertTrue($this->owner->hasModulePermission('journeys.catalog'));
        $this->assertFalse($this->owner->hasModulePermission('partner_management'));
        $this->assertFalse($this->owner->hasModulePermission('strategy'));
    }

    public function test_owner_nav_shows_only_journey_sections(): void
    {
        $registry = app(ModuleRegistry::class);
        $sections = $registry->getAccessibleSections($this->owner);

        $features = collect($sections)->pluck('feature')->unique()->values()->all();

        $this->assertSame(['journeys'], $features);
        $this->assertCount(2, $sections, 'Expected exactly two sections: My Journeys + Asset Catalog');
    }

    public function test_admin_nav_still_includes_many_sections(): void
    {
        $registry = app(ModuleRegistry::class);
        $sections = $registry->getAccessibleSections($this->admin);

        $features = collect($sections)->pluck('feature')->unique();

        $this->assertGreaterThan(1, $features->count(), 'Admin should bypass and see many features');
        $this->assertTrue($features->contains('journeys'));
        $this->assertTrue($features->contains('partner_management'));
    }

    public function test_owner_can_load_my_journeys_page(): void
    {
        $journey = Journey::factory()->create();
        JourneyAssignment::create(['journey_id' => $journey->id, 'user_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->get(route('journeys.my'));

        $response->assertOk()->assertViewIs('my-journeys.index');
        $response->assertSee($journey->name);
    }

    public function test_owner_my_journeys_excludes_unassigned_journeys(): void
    {
        $assigned = Journey::factory()->create(['name' => 'Assigned One']);
        $unassigned = Journey::factory()->create(['name' => 'Other Journey']);
        JourneyAssignment::create(['journey_id' => $assigned->id, 'user_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->get(route('journeys.my'));

        $response->assertOk()
            ->assertSee('Assigned One')
            ->assertDontSee('Other Journey');
    }

    public function test_owner_can_load_asset_catalog(): void
    {
        ContentAsset::factory()->count(3)->create();

        $response = $this->actingAs($this->owner)->get(route('assets.index'));

        $response->assertOk()->assertViewIs('assets.index');
    }

    public function test_owner_blocked_from_unrelated_modules(): void
    {
        // Partner management routes are gated by module:partner_management
        // — owner has no such permission, expect a hard 403.
        $response = $this->actingAs($this->owner)->get('/partners/my');

        $response->assertForbidden();
    }

    public function test_psm_does_not_see_journeys_module_in_nav(): void
    {
        $registry = app(ModuleRegistry::class);
        $sections = $registry->getAccessibleSections($this->psm);

        $features = collect($sections)->pluck('feature')->unique();

        $this->assertFalse(
            $features->contains('journeys'),
            'PSMs should not get the journey-owner-specific nav module'
        );
    }
}
