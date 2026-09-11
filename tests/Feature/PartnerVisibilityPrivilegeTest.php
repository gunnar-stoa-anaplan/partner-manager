<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\RolePermission;
use App\Models\User;
use App\Support\VisibilityContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Increment 1: unrestricted partner visibility is an explicit privilege, not an
 * implicit consequence of a role slug. See docs/authorization-spec.md.
 */
class PartnerVisibilityPrivilegeTest extends TestCase
{
    use RefreshDatabase;

    private function scopeFor(User $user): ?array
    {
        $this->actingAs($user);
        // Fresh instance so per-request memoization never crosses assertions.
        return (new VisibilityContext)->visiblePartnerIds();
    }

    public function test_bypass_role_sees_all_partners_by_design(): void
    {
        $user = User::factory()->superAdmin()->create();
        Partner::factory()->count(2)->create();

        $this->assertNull($this->scopeFor($user), 'bypass roles are unrestricted by design');
    }

    public function test_role_without_privilege_is_restricted_to_related_partners(): void
    {
        $user = User::factory()->psm()->create();
        $owned = Partner::factory()->create(['user_id' => $user->id]);
        $other = Partner::factory()->create();

        $ids = $this->scopeFor($user);

        $this->assertNotNull($ids, 'a plain role must be restricted');
        $this->assertContains($owned->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_role_slug_alone_does_not_grant_unrestricted_visibility(): void
    {
        // CoE without the explicit grant must now be restricted — proving the
        // slug no longer implies company-wide visibility.
        $user = User::factory()->coe()->create();
        $related = Partner::factory()->create(['user_id' => $user->id]);
        $other = Partner::factory()->create();

        $ids = $this->scopeFor($user);

        $this->assertNotNull($ids, 'the coe slug alone must no longer be unrestricted');
        $this->assertContains($related->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_explicit_privilege_grants_unrestricted_visibility(): void
    {
        $user = User::factory()->coe()->create();
        Partner::factory()->count(2)->create(); // unrelated to the user

        RolePermission::create([
            'role_id' => $user->role_id,
            'module_slug' => User::PRIVILEGE_ALL_PARTNERS,
            'access_level' => 'read',
        ]);

        $this->assertNull(
            $this->scopeFor($user),
            'a role holding data_scope.all_partners is unrestricted'
        );
    }

    public function test_privilege_works_for_any_role_not_just_coe(): void
    {
        // The privilege is role-agnostic: grant it to a plain PSM.
        $user = User::factory()->psm()->create();
        Partner::factory()->count(3)->create();

        RolePermission::create([
            'role_id' => $user->role_id,
            'module_slug' => User::PRIVILEGE_ALL_PARTNERS,
            'access_level' => 'read',
        ]);

        $this->assertNull($this->scopeFor($user));
    }
}
