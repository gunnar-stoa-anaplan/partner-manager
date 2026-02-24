<?php

namespace Tests\Feature;

use App\Ai\Agents\LabDesigner;
use App\Ai\Tools\LookupExistingLabs;
use App\Ai\Tools\SearchKnowledgeBase;
use App\Models\AnaplanModel;
use App\Models\EnablementSession;
use App\Models\Lab;
use App\Models\LabCheckpoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LabDesignerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_lab_designer_returns_structured_lab(): void
    {
        LabDesigner::fake();

        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.lab-designer'), [
            'tenant' => 'ifp',
            'topic' => 'Building a revenue forecast module',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'lab' => [
                'name',
                'description',
                'instructions',
                'tenant',
                'duration_minutes',
                'checkpoints',
            ],
        ]);
    }

    public function test_lab_designer_requires_authentication(): void
    {
        $response = $this->postJson(route('api.enablement.lab-designer'), [
            'tenant' => 'ifp',
            'topic' => 'Building a revenue forecast module',
        ]);

        $response->assertUnauthorized();
    }

    public function test_lab_designer_validates_tenant(): void
    {
        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.lab-designer'), [
            'tenant' => 'invalid',
            'topic' => 'test',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('tenant');
    }

    public function test_lab_designer_requires_topic(): void
    {
        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.lab-designer'), [
            'tenant' => 'spm',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('topic');
    }

    public function test_lab_designer_accepts_all_valid_tenants(): void
    {
        LabDesigner::fake();

        Sanctum::actingAs($this->user, ['enablement:read']);

        foreach (['ifp', 'owp', 'spm'] as $tenant) {
            $response = $this->postJson(route('api.enablement.lab-designer'), [
                'tenant' => $tenant,
                'topic' => 'Test topic',
            ]);

            $response->assertOk();
        }
    }

    public function test_lab_designer_accepts_workshop_context(): void
    {
        LabDesigner::fake();

        Sanctum::actingAs($this->user, ['enablement:read']);

        $response = $this->postJson(route('api.enablement.lab-designer'), [
            'tenant' => 'owp',
            'topic' => 'Workforce capacity modeling',
            'workshop_context' => 'OWP Best Practices Workshop covering proven patterns for workforce planning.',
            'duration_minutes' => 60,
        ]);

        $response->assertOk();
    }

    public function test_lab_designer_agent_receives_prompt_with_tenant(): void
    {
        LabDesigner::fake();

        Sanctum::actingAs($this->user, ['enablement:read']);

        $this->postJson(route('api.enablement.lab-designer'), [
            'tenant' => 'spm',
            'topic' => 'Territory planning with hierarchies',
        ]);

        LabDesigner::assertPrompted(function (AgentPrompt $prompt) {
            return $prompt->contains('SPM') && $prompt->contains('Territory planning');
        });
    }

    public function test_lab_designer_instructions_contain_anaplan_concepts(): void
    {
        $designer = new LabDesigner;
        $instructions = $designer->instructions();

        $this->assertStringContainsString('IFP', $instructions);
        $this->assertStringContainsString('OWP', $instructions);
        $this->assertStringContainsString('SPM', $instructions);
        $this->assertStringContainsString('formula', $instructions);
        $this->assertStringContainsString('structure', $instructions);
        $this->assertStringContainsString('SUM', $instructions);
        $this->assertStringContainsString('LOOKUP', $instructions);
        $this->assertStringContainsString('Citation & Grounding Rules', $instructions);
    }

    public function test_lab_designer_includes_workshop_context_in_instructions(): void
    {
        $context = 'IFP Advanced Modeling Workshop focusing on complex formula patterns.';
        $designer = new LabDesigner(workshopContext: $context);
        $instructions = $designer->instructions();

        $this->assertStringContainsString($context, $instructions);
        $this->assertStringContainsString('Workshop Context', $instructions);
    }

    public function test_lab_designer_has_lookup_tool(): void
    {
        $designer = new LabDesigner;
        $tools = iterator_to_array($designer->tools());

        $this->assertCount(2, $tools);
        $this->assertInstanceOf(LookupExistingLabs::class, $tools[0]);
        $this->assertInstanceOf(SearchKnowledgeBase::class, $tools[1]);
    }

    public function test_lab_lookup_tool_returns_labs(): void
    {
        $session = EnablementSession::factory()->create();
        $model = AnaplanModel::factory()->create();

        $lab = Lab::create([
            'enablement_session_id' => $session->id,
            'golden_model_id' => $model->id,
            'name' => 'IFP Revenue Model Lab',
            'description' => 'Build a revenue forecast from scratch.',
            'instructions' => 'Follow the steps to create a revenue module.',
            'tenant' => 'ifp',
            'status' => 'published',
            'duration_minutes' => 45,
            'created_by' => $this->user->id,
        ]);

        LabCheckpoint::create([
            'lab_id' => $lab->id,
            'title' => 'Create Revenue Module',
            'description' => 'Create a module named Revenue Forecast.',
            'sort_order' => 1,
            'checkpoint_type' => 'structure',
            'validation_config' => ['module_name' => 'Revenue Forecast'],
            'is_required' => true,
            'points' => 20,
            'hint' => 'Use the module creation dialog.',
        ]);

        $tool = new LookupExistingLabs;
        $result = $tool->handle(new \Laravel\Ai\Tools\Request(['tenant' => 'ifp']));

        $this->assertStringContainsString('IFP Revenue Model Lab', $result);
        $this->assertStringContainsString('structure: 1', $result);
        $this->assertStringContainsString('Total points: 20', $result);
    }

    public function test_lab_lookup_tool_returns_empty_message(): void
    {
        $tool = new LookupExistingLabs;
        $result = $tool->handle(new \Laravel\Ai\Tools\Request(['tenant' => 'spm']));

        $this->assertStringContainsString('No labs found', $result);
    }
}
