<?php

namespace Tests\Feature;

use App\Contracts\ChatServiceInterface;
use App\Models\AppSetting;
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

    // ─── AI Configuration ────────────────────────────────────────

    public function test_guest_cannot_update_ai_config(): void
    {
        $response = $this->put(route('configuration.update-ai'), [
            'ai_provider' => 'openai',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_set_ai_provider(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('configuration.update-ai'), [
                'ai_provider' => 'openai',
                'ai_model' => 'gpt-4o',
            ]);

        $response->assertRedirect(route('configuration.index'));
        $response->assertSessionHas('success');
        $this->assertEquals('openai', AppSetting::get('ai_provider'));
        $this->assertEquals('gpt-4o', AppSetting::get('ai_model'));
    }

    public function test_admin_can_set_ai_api_key_encrypted(): void
    {
        $this->actingAs($this->admin)
            ->put(route('configuration.update-ai'), [
                'ai_provider' => 'anthropic',
                'ai_api_key' => 'sk-ant-test-key-123',
                'ai_model' => 'claude-sonnet-4-5-20250929',
            ]);

        // Raw value in DB should not be the plaintext key
        $raw = AppSetting::get('ai_api_key');
        $this->assertNotEquals('sk-ant-test-key-123', $raw);
        $this->assertNotEmpty($raw);

        // Decrypted value should match
        $this->assertEquals('sk-ant-test-key-123', AppSetting::getEncrypted('ai_api_key'));
    }

    public function test_blank_api_key_preserves_existing(): void
    {
        AppSetting::setEncrypted('ai_api_key', 'existing-key');

        $this->actingAs($this->admin)
            ->put(route('configuration.update-ai'), [
                'ai_provider' => 'openai',
                'ai_api_key' => '',
                'ai_model' => 'gpt-4o',
            ]);

        $this->assertEquals('existing-key', AppSetting::getEncrypted('ai_api_key'));
    }

    public function test_ai_provider_validation_rejects_invalid(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('configuration.update-ai'), [
                'ai_provider' => 'invalid_provider',
            ]);

        $response->assertSessionHasErrors('ai_provider');
    }

    public function test_simulated_provider_clears_provider_setting(): void
    {
        AppSetting::set('ai_provider', 'openai');

        $this->actingAs($this->admin)
            ->put(route('configuration.update-ai'), [
                'ai_provider' => '',
            ]);

        $this->assertEquals('', AppSetting::get('ai_provider'));
    }

    public function test_configuration_page_shows_ai_card(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('configuration.index'));

        $response->assertStatus(200);
        $response->assertSee('AI Assistant');
        $response->assertSee('Provider');
    }
}
