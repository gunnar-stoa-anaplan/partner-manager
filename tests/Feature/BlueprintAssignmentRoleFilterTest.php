<?php

namespace Tests\Feature;

use App\Models\Blueprint;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithActivationSetup;

class BlueprintAssignmentRoleFilterTest extends TestCase
{
    use RefreshDatabase, WithActivationSetup;

    private User $admin;

    private Blueprint $blueprint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->activateModuleForUser('enablement', $this->admin);

        $this->blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);

        // Create assignments with different roles
        $partners = Partner::factory()->count(3)->create(['user_id' => $this->admin->id]);
        PartnerBlueprintAssignment::factory()->create([
            'blueprint_id' => $this->blueprint->id,
            'partner_id' => $partners[0]->id,
            'role' => 'gtm',
        ]);
        PartnerBlueprintAssignment::factory()->create([
            'blueprint_id' => $this->blueprint->id,
            'partner_id' => $partners[1]->id,
            'role' => 'pre_sales',
        ]);
        PartnerBlueprintAssignment::factory()->create([
            'blueprint_id' => $this->blueprint->id,
            'partner_id' => $partners[2]->id,
            'role' => 'delivery',
        ]);
    }

    public function test_blueprint_show_page_renders_role_filter_buttons(): void
    {
        $response = $this->actingAs($this->admin)->get(route('blueprints.show', $this->blueprint));

        $response->assertOk();
        $response->assertSee('id="roleFilter"', false);
        $response->assertSee('data-filter="all"', false);
        $response->assertSee('data-filter="gtm"', false);
        $response->assertSee('data-filter="pre_sales"', false);
        $response->assertSee('data-filter="delivery"', false);
    }

    public function test_role_badges_have_data_role_attributes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('blueprints.show', $this->blueprint));

        $response->assertOk();
        $response->assertSee('data-role="gtm"', false);
        $response->assertSee('data-role="pre_sales"', false);
        $response->assertSee('data-role="delivery"', false);
    }

    public function test_css_has_selector_filter_rules_are_present(): void
    {
        $response = $this->actingAs($this->admin)->get(route('blueprints.show', $this->blueprint));

        $response->assertOk();
        $response->assertSee('tr:not(:has([data-role="gtm"]))', false);
        $response->assertSee('tr:not(:has([data-role="pre_sales"]))', false);
        $response->assertSee('tr:not(:has([data-role="delivery"]))', false);
        $response->assertSee('display: none !important', false);
    }

    public function test_js_sets_data_role_filter_attribute_on_table(): void
    {
        $response = $this->actingAs($this->admin)->get(route('blueprints.show', $this->blueprint));

        $response->assertOk();
        $response->assertSee("table.setAttribute('data-role-filter'", false);
        $response->assertSee("table.removeAttribute('data-role-filter')", false);
    }
}
