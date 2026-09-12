<?php

namespace Tests\Feature;

use App\Mcp\Tools\AnalyticsSummaryTool;
use App\Mcp\Tools\IntegrationStatusTool;
use App\Models\Contact;
use App\Models\Partner;
use App\Models\User;
use App\Support\VisibilityContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Request;
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

    /**
     * analytics_summary aggregates several models. All but Contact carry the
     * ScopedToActor global scope; Contact is the portal auth model and can only
     * be scoped via its LOCAL scopeVisibleToActor. Regression: the tool called a
     * bare Contact::count(), leaking the org-wide contact total to a restricted
     * caller while every other figure was correctly scoped.
     */
    public function test_analytics_summary_contact_count_is_scoped(): void
    {
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        $other = Partner::factory()->create(); // out of scope

        Contact::factory()->count(2)->create(['partner_id' => $mine->id]);
        Contact::factory()->count(5)->create(['partner_id' => $other->id]);

        $this->actingAs($psm, 'sanctum');

        $payload = json_decode(
            (new AnalyticsSummaryTool)->handle(new Request([]))->content()->toArray()['text'],
            true,
        );

        $this->assertSame(1, $payload['partners']['total'], 'partner total must be scoped');
        $this->assertSame(
            2,
            $payload['enablement']['total_contacts'],
            'contact total must count only the caller-visible partners, not the org'
        );
    }

    /**
     * integration_status is org-level infrastructure with no partner axis, so it
     * is capability-gated (mirroring the web Integration Hub gate) rather than
     * row-scoped: a plain PSM is refused, an admin is served.
     */
    public function test_integration_status_is_capability_gated(): void
    {
        $psm = User::factory()->psm()->create();
        $this->actingAs($psm, 'sanctum');
        $this->app->forgetInstance(VisibilityContext::class);
        $denied = (new IntegrationStatusTool)->handle(new Request([]));
        $this->assertTrue($denied->isError(), 'a PSM without Integration Hub access must be refused');

        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin, 'sanctum');
        $this->app->forgetInstance(VisibilityContext::class);
        $allowed = (new IntegrationStatusTool)->handle(new Request([]));
        $this->assertFalse($allowed->isError(), 'an admin/unrestricted caller must be served');
    }
}
