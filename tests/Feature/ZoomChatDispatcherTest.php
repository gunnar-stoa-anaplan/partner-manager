<?php

namespace Tests\Feature;

use App\Ai\Agents\ProductLine\SupplyChainExpert;
use App\Enums\ZoomChatMessageStatus;
use App\Models\ZoomChatMessage;
use App\Models\ZoomChatResponder;
use App\Services\KnowledgeBaseSearchService;
use App\Services\ZoomApiService;
use App\Services\ZoomChatDispatcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Laravel\Ai\Prompts\AgentPrompt;
use Mockery;
use Tests\TestCase;

class ZoomChatDispatcherTest extends TestCase
{
    use RefreshDatabase;

    private ZoomApiService $zoomApi;

    private ZoomChatDispatcherService $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zoomApi = Mockery::mock(ZoomApiService::class);
        $this->app->instance(ZoomApiService::class, $this->zoomApi);

        $this->dispatcher = new ZoomChatDispatcherService($this->zoomApi);

        // Mock knowledge base search to return empty results
        $kbMock = Mockery::mock(KnowledgeBaseSearchService::class);
        $kbMock->shouldReceive('search')->andReturn(new Collection);
        $this->app->instance(KnowledgeBaseSearchService::class, $kbMock);
    }

    public function test_process_message_sends_agent_response_to_zoom(): void
    {
        SupplyChainExpert::fake(['Safety stock is a buffer inventory kept to mitigate supply variability.']);

        $this->zoomApi->shouldReceive('sendChatMessage')
            ->once()
            ->withArgs(function (string $meetingId, string $message) {
                return $meetingId === '12345678901'
                    && str_contains($message, 'Safety stock is a buffer inventory');
            })
            ->andReturn(true);

        $responder = ZoomChatResponder::factory()->create([
            'zoom_meeting_id' => '12345678901',
            'agent_class' => SupplyChainExpert::class,
            'display_name' => 'SCM Bot',
            'trigger_prefix' => '@bot',
        ]);

        $message = ZoomChatMessage::factory()->create([
            'zoom_chat_responder_id' => $responder->id,
            'message_text' => '@bot What is safety stock?',
            'agent_class' => SupplyChainExpert::class,
            'status' => ZoomChatMessageStatus::Received,
        ]);

        $this->dispatcher->processMessage($message);

        $message->refresh();

        $this->assertEquals(ZoomChatMessageStatus::Responded, $message->status);
        $this->assertStringContainsString('Safety stock is a buffer inventory', $message->response_text);
        $this->assertNotNull($message->processing_time_ms);
        $this->assertNull($message->error_message);
    }

    public function test_process_message_marks_failed_when_zoom_send_fails(): void
    {
        SupplyChainExpert::fake(['Agent produced a response.']);

        $this->zoomApi->shouldReceive('sendChatMessage')
            ->once()
            ->andReturn(false);

        $responder = ZoomChatResponder::factory()->create([
            'agent_class' => SupplyChainExpert::class,
        ]);

        $message = ZoomChatMessage::factory()->create([
            'zoom_chat_responder_id' => $responder->id,
            'message_text' => '@bot test question',
            'agent_class' => SupplyChainExpert::class,
            'status' => ZoomChatMessageStatus::Received,
        ]);

        $this->dispatcher->processMessage($message);

        $message->refresh();

        $this->assertEquals(ZoomChatMessageStatus::Failed, $message->status);
        $this->assertEquals('Failed to send message to Zoom.', $message->error_message);
    }

    public function test_process_message_skips_deactivated_responder(): void
    {
        $responder = ZoomChatResponder::factory()->inactive()->create([
            'agent_class' => SupplyChainExpert::class,
        ]);

        $message = ZoomChatMessage::factory()->create([
            'zoom_chat_responder_id' => $responder->id,
            'status' => ZoomChatMessageStatus::Received,
        ]);

        $this->dispatcher->processMessage($message);

        $message->refresh();

        $this->assertEquals(ZoomChatMessageStatus::Skipped, $message->status);
        $this->assertEquals('Responder was deactivated before processing.', $message->error_message);
    }

    public function test_process_message_strips_trigger_prefix_before_prompting(): void
    {
        SupplyChainExpert::fake(['Response about forecasting.']);

        $this->zoomApi->shouldReceive('sendChatMessage')
            ->once()
            ->andReturn(true);

        $responder = ZoomChatResponder::factory()->create([
            'agent_class' => SupplyChainExpert::class,
            'trigger_prefix' => '@scm',
            'display_name' => 'SCM Bot',
        ]);

        $message = ZoomChatMessage::factory()->create([
            'zoom_chat_responder_id' => $responder->id,
            'message_text' => '@scm How does forecasting work?',
            'agent_class' => SupplyChainExpert::class,
            'status' => ZoomChatMessageStatus::Received,
        ]);

        $this->dispatcher->processMessage($message);

        SupplyChainExpert::assertPrompted(function (AgentPrompt $prompt) {
            return $prompt->prompt === 'How does forecasting work?';
        });
    }

    public function test_process_message_formats_response_with_display_name(): void
    {
        SupplyChainExpert::fake(['Demand planning involves analyzing historical data.']);

        $this->zoomApi->shouldReceive('sendChatMessage')
            ->once()
            ->withArgs(function (string $meetingId, string $message) {
                return str_starts_with($message, '[Supply Chain Bot] Demand planning involves');
            })
            ->andReturn(true);

        $responder = ZoomChatResponder::factory()->create([
            'agent_class' => SupplyChainExpert::class,
            'display_name' => 'Supply Chain Bot',
            'trigger_prefix' => '@bot',
        ]);

        $message = ZoomChatMessage::factory()->create([
            'zoom_chat_responder_id' => $responder->id,
            'message_text' => '@bot Tell me about demand planning',
            'agent_class' => SupplyChainExpert::class,
            'status' => ZoomChatMessageStatus::Received,
        ]);

        $this->dispatcher->processMessage($message);

        $message->refresh();
        $this->assertEquals(ZoomChatMessageStatus::Responded, $message->status);
    }

    public function test_process_message_captures_error_on_agent_exception(): void
    {
        SupplyChainExpert::fake(function () {
            throw new \RuntimeException('Model API temporarily unavailable');
        });

        $responder = ZoomChatResponder::factory()->create([
            'agent_class' => SupplyChainExpert::class,
        ]);

        $message = ZoomChatMessage::factory()->create([
            'zoom_chat_responder_id' => $responder->id,
            'message_text' => '@bot This will fail',
            'agent_class' => SupplyChainExpert::class,
            'status' => ZoomChatMessageStatus::Received,
        ]);

        $this->dispatcher->processMessage($message);

        $message->refresh();

        $this->assertEquals(ZoomChatMessageStatus::Failed, $message->status);
        $this->assertStringContainsString('Model API temporarily unavailable', $message->error_message);
        $this->assertNotNull($message->processing_time_ms);
    }

    public function test_should_respond_returns_false_for_inactive_responder(): void
    {
        $responder = ZoomChatResponder::factory()->inactive()->create();

        $this->assertFalse($this->dispatcher->shouldRespond($responder, '@bot hello'));
    }

    public function test_should_respond_returns_false_for_wrong_trigger(): void
    {
        $responder = ZoomChatResponder::factory()->create([
            'trigger_prefix' => '@scm',
        ]);

        $this->assertFalse($this->dispatcher->shouldRespond($responder, '@finance What is ROI?'));
    }

    public function test_should_respond_returns_true_for_matching_trigger(): void
    {
        $responder = ZoomChatResponder::factory()->create([
            'trigger_prefix' => '@bot',
            'is_active' => true,
        ]);

        $this->assertTrue($this->dispatcher->shouldRespond($responder, '@bot What is safety stock?'));
    }
}
