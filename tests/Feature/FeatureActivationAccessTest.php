<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class FeatureActivationAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $coe;

    private User $psm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->coe = User::factory()->create(['role' => 'coe']);
        $this->psm = User::factory()->create(['role' => 'psm']);
    }

    public function test_admin_can_access_feature_flags(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.activation.flags'));

        $response->assertOk();
    }

    public function test_admin_can_access_activation_paths(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.activation.paths'));

        $response->assertOk();
    }

    public function test_admin_can_access_progression(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.activation.progression'));

        $response->assertOk();
    }

    public function test_coe_without_flag_cannot_access_feature_flags(): void
    {
        Feature::for(null)->deactivate('feature_activation_coe');

        $response = $this->actingAs($this->coe)->get(route('admin.activation.flags'));

        $response->assertForbidden();
    }

    public function test_coe_with_flag_can_access_feature_flags(): void
    {
        Feature::for(null)->activate('feature_activation_coe');

        $response = $this->actingAs($this->coe)->get(route('admin.activation.flags'));

        $response->assertOk();
    }

    public function test_coe_with_flag_can_access_activation_paths(): void
    {
        Feature::for(null)->activate('feature_activation_coe');

        $response = $this->actingAs($this->coe)->get(route('admin.activation.paths'));

        $response->assertOk();
    }

    public function test_coe_with_flag_can_access_progression(): void
    {
        Feature::for(null)->activate('feature_activation_coe');

        $response = $this->actingAs($this->coe)->get(route('admin.activation.progression'));

        $response->assertOk();
    }

    public function test_psm_cannot_access_feature_flags(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.activation.flags'));

        $response->assertForbidden();
    }

    public function test_psm_cannot_access_activation_paths(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.activation.paths'));

        $response->assertForbidden();
    }

    public function test_psm_cannot_access_progression(): void
    {
        $response = $this->actingAs($this->psm)->get(route('admin.activation.progression'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_from_feature_flags(): void
    {
        $response = $this->get(route('admin.activation.flags'));

        $response->assertRedirect(route('login'));
    }
}
