<?php

namespace Tests\Feature;

use App\Contracts\ChatServiceInterface;
use App\Models\Partner;
use App\Models\User;
use App\Services\ChatContextService;
use App\Services\SimulatedChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
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

    // ─── Access Control ──────────────────────────────────────────

    public function test_guest_cannot_access_chat(): void
    {
        $response = $this->postJson('/api/chat', ['message' => 'hello']);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_chat(): void
    {
        $response = $this->actingAs($this->psm)
            ->post('/api/chat', ['message' => 'hello']);

        $response->assertStatus(200);
        $response->assertStreamed();
    }

    // ─── Validation ──────────────────────────────────────────────

    public function test_chat_requires_message(): void
    {
        $response = $this->actingAs($this->psm)
            ->postJson('/api/chat', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    public function test_chat_message_max_length(): void
    {
        $response = $this->actingAs($this->psm)
            ->postJson('/api/chat', ['message' => str_repeat('a', 1001)]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    public function test_chat_message_must_be_string(): void
    {
        $response = $this->actingAs($this->psm)
            ->postJson('/api/chat', ['message' => 12345]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    // ─── Streaming Responses ─────────────────────────────────────

    public function test_chat_responds_to_pipeline_query(): void
    {
        $response = $this->actingAs($this->psm)
            ->post('/api/chat', ['message' => 'Show me my pipeline']);

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('pipeline', strtolower($content));
    }

    public function test_chat_responds_to_health_query(): void
    {
        $response = $this->actingAs($this->psm)
            ->post('/api/chat', ['message' => 'Which partners are at risk?']);

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('health', strtolower($content));
    }

    public function test_chat_responds_to_goal_query(): void
    {
        $response = $this->actingAs($this->psm)
            ->post('/api/chat', ['message' => 'How are my goals tracking?']);

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('goal', strtolower($content));
    }

    public function test_chat_responds_to_task_query(): void
    {
        $response = $this->actingAs($this->psm)
            ->post('/api/chat', ['message' => 'Do I have any overdue tasks?']);

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('task', strtolower($content));
    }

    public function test_chat_responds_to_certification_query(): void
    {
        $response = $this->actingAs($this->psm)
            ->post('/api/chat', ['message' => 'Show certification status']);

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('certification', strtolower($content));
    }

    public function test_chat_provides_overview_for_generic_query(): void
    {
        $response = $this->actingAs($this->psm)
            ->post('/api/chat', ['message' => 'hello']);

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('portfolio', strtolower($content));
    }

    // ─── Context Scoping ─────────────────────────────────────────

    public function test_psm_only_sees_own_partners(): void
    {
        $ownPartner = Partner::factory()->create(['user_id' => $this->psm->id, 'name' => 'OwnPartnerCo']);
        Partner::factory()->create(['user_id' => $this->admin->id, 'name' => 'OtherPartnerCo']);

        $contextService = app(ChatContextService::class);
        $context = $contextService->buildContext($this->psm);

        $this->assertEquals(1, $context['partner_count']);
        $this->assertEquals('OwnPartnerCo', $context['partners'][0]['name']);
    }

    public function test_admin_sees_all_partners(): void
    {
        Partner::factory()->create(['user_id' => $this->psm->id, 'name' => 'PartnerA']);
        Partner::factory()->create(['user_id' => $this->admin->id, 'name' => 'PartnerB']);
        Partner::factory()->create(['user_id' => null, 'name' => 'PartnerC']);

        $contextService = app(ChatContextService::class);
        $context = $contextService->buildContext($this->admin);

        $this->assertEquals(3, $context['partner_count']);
    }

    // ─── Service Interface Binding ───────────────────────────────

    public function test_chat_service_interface_is_bound(): void
    {
        $service = app(ChatServiceInterface::class);
        $this->assertInstanceOf(SimulatedChatService::class, $service);
    }
}
