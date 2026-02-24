<?php

namespace Tests\Feature;

use App\Ai\Agents\EnablementDesigner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnablementEventGenerateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->psm = User::factory()->create(['role' => 'psm']);
    }

    public function test_generate_requires_authentication(): void
    {
        $response = $this->postJson(route('enablement-events.generate'), [
            'product_area' => 'IFP',
            'type' => 'workshop',
        ]);

        $response->assertUnauthorized();
    }

    public function test_generate_requires_product_area(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('enablement-events.generate'), [
            'type' => 'workshop',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('product_area');
    }

    public function test_generate_requires_type(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('enablement-events.generate'), [
            'product_area' => 'IFP',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('type');
    }

    public function test_generate_validates_type(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('enablement-events.generate'), [
            'product_area' => 'IFP',
            'type' => 'invalid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('type');
    }

    public function test_generate_returns_workshop_json(): void
    {
        EnablementDesigner::fake();

        $response = $this->actingAs($this->admin)->postJson(route('enablement-events.generate'), [
            'product_area' => 'IFP',
            'type' => 'workshop',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'workshop' => [
                'title',
                'type',
                'category',
                'product_area',
                'target_audience',
                'duration_hours',
                'max_participants',
                'description',
                'objectives',
                'prerequisites',
            ],
        ]);
    }

    public function test_generate_accepts_optional_topic_and_audience(): void
    {
        EnablementDesigner::fake();

        $response = $this->actingAs($this->admin)->postJson(route('enablement-events.generate'), [
            'product_area' => 'OWP',
            'type' => 'bootcamp',
            'topic' => 'Workforce capacity modeling',
            'audience' => 'Solution Architects',
        ]);

        $response->assertOk();
    }
}
