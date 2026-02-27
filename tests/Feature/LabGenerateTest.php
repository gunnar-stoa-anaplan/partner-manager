<?php

namespace Tests\Feature;

use App\Ai\Agents\LabDesigner;
use App\Models\AnaplanModel;
use App\Models\EnablementSession;
use App\Models\LabCheckpoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabGenerateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_generate_requires_authentication(): void
    {
        $response = $this->postJson(route('labs.generate'), [
            'tenant' => 'ifp',
            'topic' => 'Revenue forecasting',
        ]);

        $response->assertUnauthorized();
    }

    public function test_generate_requires_tenant(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('labs.generate'), [
            'topic' => 'Revenue forecasting',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('tenant');
    }

    public function test_generate_requires_topic(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('labs.generate'), [
            'tenant' => 'ifp',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('topic');
    }

    public function test_generate_validates_tenant(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('labs.generate'), [
            'tenant' => 'invalid',
            'topic' => 'test',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('tenant');
    }

    public function test_generate_returns_lab_with_checkpoints(): void
    {
        LabDesigner::fake();

        $response = $this->actingAs($this->admin)->postJson(route('labs.generate'), [
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

    public function test_store_with_checkpoints_json_creates_checkpoints(): void
    {
        $session = EnablementSession::factory()->create();
        $model = AnaplanModel::factory()->create();

        $checkpoints = [
            [
                'sort_order' => 1,
                'title' => 'Create Revenue Module',
                'description' => 'Create a module named Revenue Forecast.',
                'checkpoint_type' => 'structure',
                'points' => 20,
                'hint' => 'Use the module creation dialog.',
                'is_required' => true,
            ],
            [
                'sort_order' => 2,
                'title' => 'Add Growth Rate Formula',
                'description' => 'Add a formula line item for growth rate.',
                'checkpoint_type' => 'formula',
                'points' => 30,
                'hint' => 'Use the IF THEN ELSE function.',
                'is_required' => true,
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('labs.store'), [
            'enablement_session_id' => $session->id,
            'name' => 'AI-Generated Lab',
            'description' => 'Test lab',
            'instructions' => 'Follow the steps',
            'golden_model_id' => $model->id,
            'tenant' => 'ifp',
            'duration_minutes' => 45,
            'checkpoints_json' => json_encode($checkpoints),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('labs', ['name' => 'AI-Generated Lab']);
        $this->assertDatabaseHas('lab_checkpoints', ['title' => 'Create Revenue Module', 'points' => 20]);
        $this->assertDatabaseHas('lab_checkpoints', ['title' => 'Add Growth Rate Formula', 'points' => 30]);
    }

    public function test_store_without_checkpoints_json_works_normally(): void
    {
        $session = EnablementSession::factory()->create();
        $model = AnaplanModel::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('labs.store'), [
            'enablement_session_id' => $session->id,
            'name' => 'Manual Lab',
            'golden_model_id' => $model->id,
            'tenant' => 'owp',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('labs', ['name' => 'Manual Lab']);
        $this->assertEquals(0, LabCheckpoint::count());
    }
}
