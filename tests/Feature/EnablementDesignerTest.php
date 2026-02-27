<?php

namespace Tests\Feature;

use App\Ai\Agents\EnablementDesigner;
use App\Ai\Tools\ConsultProductExpert;
use App\Ai\Tools\LookupEnablementCatalog;
use App\Ai\Tools\SearchKnowledgeBase;
use App\Models\EnablementEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnablementDesignerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->admin()->create();
    }

    public function test_designer_returns_structured_workshop(): void
    {
        EnablementDesigner::fake();

        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.designer'), [
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
                'agenda',
                'lab_design',
                'promotion_copy',
                'instructor_notes',
            ],
        ]);
    }

    public function test_designer_requires_authentication(): void
    {
        $response = $this->postJson(route('api.enablement.designer'), [
            'product_area' => 'IFP',
            'type' => 'workshop',
        ]);

        $response->assertUnauthorized();
    }

    public function test_designer_validates_product_area(): void
    {
        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.designer'), [
            'product_area' => 'INVALID',
            'type' => 'workshop',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('product_area');
    }

    public function test_designer_validates_type(): void
    {
        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.designer'), [
            'product_area' => 'SPM',
            'type' => 'invalid_type',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('type');
    }

    public function test_designer_requires_product_area_and_type(): void
    {
        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.designer'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['product_area', 'type']);
    }

    public function test_designer_accepts_optional_topic_and_audience(): void
    {
        EnablementDesigner::fake();

        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.designer'), [
            'product_area' => 'OWP',
            'type' => 'bootcamp',
            'topic' => 'Advanced workforce modeling with seasonal patterns',
            'audience' => 'Solution Architects',
        ]);

        $response->assertOk();
    }

    public function test_designer_agent_receives_prompt_with_product_area(): void
    {
        EnablementDesigner::fake();

        Sanctum::actingAs($this->user, ['enablement:read']);

        $this->postJson(route('api.enablement.designer'), [
            'product_area' => 'SCM',
            'type' => 'academy',
        ]);

        EnablementDesigner::assertPrompted(function (AgentPrompt $prompt) {
            return $prompt->contains('SCM') && $prompt->contains('Academy');
        });
    }

    public function test_designer_instructions_contain_product_areas(): void
    {
        $designer = new EnablementDesigner;
        $instructions = $designer->instructions();

        $this->assertStringContainsString('IFP', $instructions);
        $this->assertStringContainsString('SCM', $instructions);
        $this->assertStringContainsString('SPM', $instructions);
        $this->assertStringContainsString('OWP', $instructions);
        $this->assertStringContainsString('Tell-Show-Do', $instructions);
        $this->assertStringContainsString('Citation & Grounding Rules', $instructions);
    }

    public function test_designer_instructions_reference_consult_product_expert(): void
    {
        $designer = new EnablementDesigner;
        $instructions = (string) $designer->instructions();

        $this->assertStringContainsString('ConsultProductExpert', $instructions);
    }

    public function test_designer_has_catalog_lookup_tool(): void
    {
        $designer = new EnablementDesigner;
        $tools = iterator_to_array($designer->tools());

        $this->assertCount(3, $tools);
        $this->assertInstanceOf(LookupEnablementCatalog::class, $tools[0]);
        $this->assertInstanceOf(ConsultProductExpert::class, $tools[1]);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[2]);
    }

    public function test_catalog_tool_returns_events(): void
    {
        EnablementEvent::create([
            'title' => 'IFP Fundamentals Academy',
            'type' => 'academy',
            'category' => 'Delivery',
            'product_area' => 'IFP',
            'duration_hours' => 16,
            'max_participants' => 20,
            'target_audience' => 'Model Builders',
            'description' => 'Comprehensive IFP training.',
            'status' => 'published',
            'created_by' => $this->user->id,
        ]);

        $tool = new LookupEnablementCatalog;
        $result = $tool->handle(new \Laravel\Ai\Tools\Request(['product_area' => 'IFP']));

        $this->assertStringContainsString('IFP Fundamentals Academy', $result);
        $this->assertStringContainsString('academy', $result);
        $this->assertStringContainsString('Model Builders', $result);
    }

    public function test_catalog_tool_filters_by_type(): void
    {
        EnablementEvent::create([
            'title' => 'SPM Workshop',
            'type' => 'workshop',
            'category' => 'Delivery',
            'product_area' => 'SPM',
            'duration_hours' => 4,
            'target_audience' => 'Model Builders',
            'status' => 'published',
            'created_by' => $this->user->id,
        ]);

        EnablementEvent::create([
            'title' => 'SPM Webinar',
            'type' => 'webinar',
            'category' => 'Sales',
            'product_area' => 'SPM',
            'duration_hours' => 1,
            'target_audience' => 'Sales Engineers',
            'status' => 'published',
            'created_by' => $this->user->id,
        ]);

        $tool = new LookupEnablementCatalog;
        $result = $tool->handle(new \Laravel\Ai\Tools\Request(['type' => 'workshop']));

        $this->assertStringContainsString('SPM Workshop', $result);
        $this->assertStringNotContainsString('SPM Webinar', $result);
    }

    public function test_catalog_tool_returns_empty_message(): void
    {
        $tool = new LookupEnablementCatalog;
        $result = $tool->handle(new \Laravel\Ai\Tools\Request(['product_area' => 'IFP']));

        $this->assertStringContainsString('No published enablement events found', $result);
    }
}
