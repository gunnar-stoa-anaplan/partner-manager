<?php

namespace Tests\Feature;

use App\Ai\Agents\PartnerAdvisor;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerAdvisorWebTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->partner = Partner::factory()->create();
    }

    public function test_advisor_requires_authentication(): void
    {
        $response = $this->postJson(route('partners.advisor', $this->partner));

        $response->assertUnauthorized();
    }

    public function test_advisor_returns_structured_analysis(): void
    {
        PartnerAdvisor::fake();

        $response = $this->actingAs($this->admin)->postJson(route('partners.advisor', $this->partner));

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

    public function test_advisor_returns_three_recommendations(): void
    {
        PartnerAdvisor::fake();

        $response = $this->actingAs($this->admin)->postJson(route('partners.advisor', $this->partner));

        $response->assertOk();
        $response->assertJsonCount(3, 'analysis.recommendations');
    }

    public function test_advisor_returns_partner_identifiers(): void
    {
        PartnerAdvisor::fake();

        $response = $this->actingAs($this->admin)->postJson(route('partners.advisor', $this->partner));

        $response->assertOk();
        $response->assertJson([
            'partner_id' => $this->partner->id,
            'partner_name' => $this->partner->name,
        ]);
    }
}
