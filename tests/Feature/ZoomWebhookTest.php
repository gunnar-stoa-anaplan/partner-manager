<?php

namespace Tests\Feature;

use App\Enums\ZoomChatMessageStatus;
use App\Jobs\ProcessZoomChatMessage;
use App\Models\ZoomChatMessage;
use App\Models\ZoomChatResponder;
use App\Services\ZoomApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ZoomWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookUrl = '/api/webhooks/zoom/chat';

    private string $secretToken = 'test-secret-token';

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(ZoomApiService::class, function () {
            return new ZoomApiService(
                accountId: 'test-account',
                clientId: 'test-client',
                clientSecret: 'test-secret',
                webhookSecretToken: $this->secretToken,
            );
        });
    }

    private function signPayload(string $payload, string $timestamp): string
    {
        $message = "v0:{$timestamp}:{$payload}";
        $hash = hash_hmac('sha256', $message, $this->secretToken);

        return "v0={$hash}";
    }

    public function test_url_validation_challenge_returns_encrypted_token(): void
    {
        $payload = ['event' => 'endpoint.url_validation', 'payload' => ['plainToken' => 'abc123']];

        $response = $this->postJson($this->webhookUrl, $payload);

        $response->assertOk();
        $response->assertJsonStructure(['plainToken', 'encryptedToken']);
        $this->assertEquals('abc123', $response->json('plainToken'));
    }

    public function test_invalid_signature_returns_401(): void
    {
        $payload = json_encode(['event' => 'meeting.chat_message_sent', 'payload' => ['object' => []]]);

        $response = $this->postJson($this->webhookUrl, json_decode($payload, true), [
            'x-zm-signature' => 'invalid-signature',
            'x-zm-request-timestamp' => (string) time(),
        ]);

        $response->assertUnauthorized();
    }

    public function test_valid_webhook_creates_message_and_dispatches_job(): void
    {
        Queue::fake();

        $responder = ZoomChatResponder::factory()->create([
            'zoom_meeting_id' => '12345678901',
            'is_active' => true,
            'trigger_prefix' => '@bot',
        ]);

        $payload = [
            'event' => 'meeting.chat_message_sent',
            'payload' => [
                'object' => [
                    'id' => '12345678901',
                    'chat_message' => [
                        'message_id' => 'msg-uuid-001',
                        'message' => '@bot What is safety stock?',
                        'sender' => [
                            'name' => 'Jane Doe',
                            'email' => 'jane@example.com',
                        ],
                    ],
                ],
            ],
        ];

        $json = json_encode($payload);
        $timestamp = (string) time();
        $signature = $this->signPayload($json, $timestamp);

        $response = $this->call('POST', $this->webhookUrl, [], [], [], [
            'HTTP_X_ZM_SIGNATURE' => $signature,
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
            'CONTENT_TYPE' => 'application/json',
        ], $json);

        $response->assertOk();
        $response->assertJson(['status' => 'queued']);

        $this->assertDatabaseHas('zoom_chat_messages', [
            'zoom_message_id' => 'msg-uuid-001',
            'sender_name' => 'Jane Doe',
            'message_text' => '@bot What is safety stock?',
            'status' => ZoomChatMessageStatus::Received->value,
        ]);

        Queue::assertPushed(ProcessZoomChatMessage::class);
    }

    public function test_message_without_trigger_prefix_is_skipped(): void
    {
        Queue::fake();

        $responder = ZoomChatResponder::factory()->create([
            'zoom_meeting_id' => '12345678901',
            'is_active' => true,
            'trigger_prefix' => '@bot',
        ]);

        $payload = [
            'event' => 'meeting.chat_message_sent',
            'payload' => [
                'object' => [
                    'id' => '12345678901',
                    'chat_message' => [
                        'message_id' => 'msg-uuid-002',
                        'message' => 'Hello everyone!',
                        'sender' => ['name' => 'John', 'email' => null],
                    ],
                ],
            ],
        ];

        $json = json_encode($payload);
        $timestamp = (string) time();
        $signature = $this->signPayload($json, $timestamp);

        $response = $this->call('POST', $this->webhookUrl, [], [], [], [
            'HTTP_X_ZM_SIGNATURE' => $signature,
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
            'CONTENT_TYPE' => 'application/json',
        ], $json);

        $response->assertOk();
        $response->assertJson(['status' => 'skipped']);

        $this->assertDatabaseHas('zoom_chat_messages', [
            'zoom_message_id' => 'msg-uuid-002',
            'status' => ZoomChatMessageStatus::Skipped->value,
        ]);

        Queue::assertNotPushed(ProcessZoomChatMessage::class);
    }

    public function test_duplicate_message_is_ignored(): void
    {
        Queue::fake();

        $responder = ZoomChatResponder::factory()->create([
            'zoom_meeting_id' => '12345678901',
            'is_active' => true,
        ]);

        ZoomChatMessage::factory()->create([
            'zoom_chat_responder_id' => $responder->id,
            'zoom_message_id' => 'msg-uuid-dup',
        ]);

        $payload = [
            'event' => 'meeting.chat_message_sent',
            'payload' => [
                'object' => [
                    'id' => '12345678901',
                    'chat_message' => [
                        'message_id' => 'msg-uuid-dup',
                        'message' => '@bot duplicate',
                        'sender' => ['name' => 'Test', 'email' => null],
                    ],
                ],
            ],
        ];

        $json = json_encode($payload);
        $timestamp = (string) time();
        $signature = $this->signPayload($json, $timestamp);

        $response = $this->call('POST', $this->webhookUrl, [], [], [], [
            'HTTP_X_ZM_SIGNATURE' => $signature,
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
            'CONTENT_TYPE' => 'application/json',
        ], $json);

        $response->assertOk();
        $response->assertJson(['status' => 'duplicate']);

        Queue::assertNotPushed(ProcessZoomChatMessage::class);
    }

    public function test_no_responder_for_meeting_returns_no_responder(): void
    {
        $payload = [
            'event' => 'meeting.chat_message_sent',
            'payload' => [
                'object' => [
                    'id' => '99999999999',
                    'chat_message' => [
                        'message_id' => 'msg-uuid-003',
                        'message' => '@bot hello',
                        'sender' => ['name' => 'Test', 'email' => null],
                    ],
                ],
            ],
        ];

        $json = json_encode($payload);
        $timestamp = (string) time();
        $signature = $this->signPayload($json, $timestamp);

        $response = $this->call('POST', $this->webhookUrl, [], [], [], [
            'HTTP_X_ZM_SIGNATURE' => $signature,
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
            'CONTENT_TYPE' => 'application/json',
        ], $json);

        $response->assertOk();
        $response->assertJson(['status' => 'no_responder']);
    }

    public function test_inactive_responder_skips_message(): void
    {
        Queue::fake();

        ZoomChatResponder::factory()->inactive()->create([
            'zoom_meeting_id' => '12345678901',
        ]);

        $payload = [
            'event' => 'meeting.chat_message_sent',
            'payload' => [
                'object' => [
                    'id' => '12345678901',
                    'chat_message' => [
                        'message_id' => 'msg-uuid-inactive',
                        'message' => '@bot hello',
                        'sender' => ['name' => 'Test', 'email' => null],
                    ],
                ],
            ],
        ];

        $json = json_encode($payload);
        $timestamp = (string) time();
        $signature = $this->signPayload($json, $timestamp);

        $response = $this->call('POST', $this->webhookUrl, [], [], [], [
            'HTTP_X_ZM_SIGNATURE' => $signature,
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
            'CONTENT_TYPE' => 'application/json',
        ], $json);

        $response->assertOk();
        $response->assertJson(['status' => 'no_responder']);

        Queue::assertNotPushed(ProcessZoomChatMessage::class);
    }
}
