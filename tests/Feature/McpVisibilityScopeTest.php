<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Support\VisibilityContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Row-level visibility must apply to API/MCP callers authenticated via Sanctum,
 * not only to session (web/portal) actors. Regression: VisibilityContext used to
 * read only the web/portal guards, so a token-authenticated request resolved to
 * no actor and was treated as unrestricted — the MCP tools then returned every
 * partner regardless of the caller's scope.
 */
class McpVisibilityScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanctum_authenticated_actor_is_partner_scoped(): void
    {
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        Partner::factory()->count(2)->create(); // other partners, out of scope

        $this->actingAs($psm, 'sanctum');

        $this->assertSame(
            [$mine->id],
            (new VisibilityContext)->visiblePartnerIds(),
            'a Sanctum-authenticated actor must resolve to a restricted partner scope'
        );
        $this->assertSame(
            [$mine->id],
            Partner::query()->pluck('id')->all(),
            'MCP tools querying Partner must be scoped for a Sanctum caller'
        );
    }

    public function test_unrestricted_privilege_still_applies_under_sanctum(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Partner::factory()->count(3)->create();

        $this->actingAs($admin, 'sanctum');

        $this->assertNull((new VisibilityContext)->visiblePartnerIds());
        $this->assertCount(3, Partner::query()->get());
    }
}
