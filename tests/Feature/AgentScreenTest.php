<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\User;
use App\Services\Agent\ScreenAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The embedded screen agent must be inert unless enabled, and — critically — its
 * facts must be built from row-level-scoped data so it can never surface figures
 * about partners the caller cannot see.
 */
class AgentScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoints_are_404_when_disabled(): void
    {
        config(['agent.enabled' => false]);
        $user = User::factory()->psm()->create();

        $this->actingAs($user)
            ->postJson(route('agent.insights'), ['screen' => 'pipeline'])
            ->assertNotFound();
    }

    public function test_pipeline_facts_are_scoped_to_the_actor(): void
    {
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        $other = Partner::factory()->create();

        Opportunity::factory()->create(['partner_id' => $mine->id, 'stage' => 'Prospecting', 'amount' => 100000, 'probability' => 50]);
        Opportunity::factory()->create(['partner_id' => $other->id, 'stage' => 'Prospecting', 'amount' => 999999, 'probability' => 50]);

        $this->actingAs($psm);
        $context = app(ScreenAgentService::class)->contextFor('pipeline');

        $this->assertSame(100000.0, $context->facts['open_pipeline_value'], 'facts must exclude out-of-scope partners');
        $this->assertSame(1, $context->facts['open_opportunity_count']);
        $this->assertFalse($context->unrestricted);
    }

    public function test_unrestricted_actor_sees_all_in_facts(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Opportunity::factory()->create(['partner_id' => Partner::factory()->create()->id, 'stage' => 'Prospecting', 'amount' => 100000, 'probability' => 50]);
        Opportunity::factory()->create(['partner_id' => Partner::factory()->create()->id, 'stage' => 'Prospecting', 'amount' => 250000, 'probability' => 50]);

        $this->actingAs($admin);
        $context = app(ScreenAgentService::class)->contextFor('pipeline');

        $this->assertSame(350000.0, $context->facts['open_pipeline_value']);
        $this->assertTrue($context->unrestricted);
    }

    public function test_insights_endpoint_returns_grounded_scoped_payload(): void
    {
        config(['agent.enabled' => true]);
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        Opportunity::factory()->create(['partner_id' => $mine->id, 'stage' => 'Prospecting', 'amount' => 100000, 'probability' => 50]);

        $res = $this->actingAs($psm)->postJson(route('agent.insights'), ['screen' => 'pipeline']);

        $res->assertOk()
            ->assertJsonPath('provider', 'simulated')
            ->assertJsonPath('scope', 'your partners');
        $this->assertNotEmpty($res->json('insights'));
    }

    public function test_ask_endpoint_answers_from_scoped_facts(): void
    {
        config(['agent.enabled' => true]);
        $psm = User::factory()->psm()->create();
        $mine = Partner::factory()->create(['user_id' => $psm->id]);
        Opportunity::factory()->create(['partner_id' => $mine->id, 'stage' => 'Prospecting', 'amount' => 100000, 'probability' => 50]);

        $res = $this->actingAs($psm)->postJson(route('agent.ask'), [
            'screen' => 'pipeline',
            'question' => 'What is my open pipeline?',
        ]);

        $res->assertOk();
        $this->assertStringContainsStringIgnoringCase('pipeline', $res->json('answer'));
    }
}
