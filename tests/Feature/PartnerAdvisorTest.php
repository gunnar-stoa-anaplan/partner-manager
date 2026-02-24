<?php

namespace Tests\Feature;

use App\Ai\Agents\PartnerAdvisor;
use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use App\Models\PartnerScorecard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PartnerAdvisorTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'psm']);
        $this->partner = Partner::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_advisor_returns_structured_analysis(): void
    {
        PartnerAdvisor::fake();

        Sanctum::actingAs($this->user, ['partners:read']);

        $response = $this->getJson(route('api.partners.advisor', $this->partner));

        $response->assertOk();
        $response->assertJsonStructure([
            'partner_id',
            'partner_name',
            'analysis' => [
                'health_score',
                'risk_level',
                'summary',
                'recommendations',
                'key_strength',
                'key_risk',
            ],
        ]);
    }

    public function test_advisor_requires_authentication(): void
    {
        $response = $this->getJson(route('api.partners.advisor', $this->partner));

        $response->assertUnauthorized();
    }

    public function test_advisor_builds_context_with_scorecard(): void
    {
        PartnerScorecard::create([
            'partner_id' => $this->partner->id,
            'scored_at' => now(),
            'overall_score' => 72.5,
            'certification_score' => 80.0,
            'pipeline_score' => 65.0,
            'delivery_score' => 70.0,
            'engagement_score' => 75.0,
            'growth_score' => 60.0,
            'self_sufficiency_score' => 85.0,
            'tier' => 'silver',
            'gate_eligible' => true,
        ]);

        PartnerHealthSnapshot::create([
            'partner_id' => $this->partner->id,
            'score' => 68.0,
            'risk_level' => 'watch',
            'signals' => [
                ['type' => 'pipeline_decline', 'label' => 'Pipeline value dropped 25%'],
            ],
            'snapshot_date' => now(),
        ]);

        $advisor = new PartnerAdvisor($this->partner->fresh());
        $instructions = $advisor->instructions();

        $this->assertStringContainsString($this->partner->name, $instructions);
        $this->assertStringContainsString('72.5', $instructions);
        $this->assertStringContainsString('silver', $instructions);
        $this->assertStringContainsString('watch', $instructions);
        $this->assertStringContainsString('Pipeline value dropped 25%', $instructions);
    }

    public function test_advisor_handles_partner_with_no_data(): void
    {
        $advisor = new PartnerAdvisor($this->partner);
        $instructions = $advisor->instructions();

        $this->assertStringContainsString($this->partner->name, $instructions);
        $this->assertStringContainsString('Projects: 0', $instructions);
        $this->assertStringContainsString('Contacts: 0', $instructions);
    }

    public function test_advisor_agent_receives_prompt(): void
    {
        PartnerAdvisor::fake(['Fake analysis response']);

        Sanctum::actingAs($this->user, ['partners:read']);

        $this->getJson(route('api.partners.advisor', $this->partner));

        PartnerAdvisor::assertPrompted('Analyze this partner and provide your health assessment with recommendations.');
    }
}
