<?php

namespace Tests\Unit;

use App\Services\ZoomApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZoomApiServiceTest extends TestCase
{
    private ZoomApiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ZoomApiService(
            accountId: 'test-account-id',
            clientId: 'test-client-id',
            clientSecret: 'test-client-secret',
            webhookSecretToken: 'test-webhook-secret',
        );
    }

    public function test_verify_webhook_signature_accepts_valid_signature(): void
    {
        $payload = '{"event":"meeting.chat_message_sent"}';
        $timestamp = '1700000000';
        $message = "v0:{$timestamp}:{$payload}";
        $hash = hash_hmac('sha256', $message, 'test-webhook-secret');
        $signature = "v0={$hash}";

        $this->assertTrue($this->service->verifyWebhookSignature($payload, $timestamp, $signature));
    }

    public function test_verify_webhook_signature_rejects_invalid_signature(): void
    {
        $this->assertFalse($this->service->verifyWebhookSignature('payload', '123', 'v0=invalid'));
    }

    public function test_handle_url_validation_returns_encrypted_token(): void
    {
        $result = $this->service->handleUrlValidation('test-plain-token');

        $this->assertEquals('test-plain-token', $result['plainToken']);
        $this->assertNotEmpty($result['encryptedToken']);

        $expected = hash_hmac('sha256', 'test-plain-token', 'test-webhook-secret');
        $this->assertEquals($expected, $result['encryptedToken']);
    }

    public function test_send_chat_message_posts_to_zoom_api(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response(['access_token' => 'fake-token'], 200),
            'api.zoom.us/v2/live_meetings/*/chat/messages' => Http::response([], 201),
        ]);

        Cache::forget('zoom_s2s_access_token');

        $result = $this->service->sendChatMessage('12345678901', 'Hello from test');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'live_meetings/12345678901/chat/messages')
                && $request['message'] === 'Hello from test';
        });
    }

    public function test_send_chat_message_returns_false_on_failure(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response(['access_token' => 'fake-token'], 200),
            'api.zoom.us/v2/live_meetings/*/chat/messages' => Http::response(['message' => 'Meeting not found'], 404),
        ]);

        Cache::forget('zoom_s2s_access_token');

        $result = $this->service->sendChatMessage('99999999999', 'This should fail');

        $this->assertFalse($result);
    }

    public function test_get_access_token_caches_result(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response(['access_token' => 'cached-token'], 200),
        ]);

        Cache::forget('zoom_s2s_access_token');

        $token1 = $this->service->getAccessToken();
        $token2 = $this->service->getAccessToken();

        $this->assertEquals('cached-token', $token1);
        $this->assertEquals('cached-token', $token2);

        // Should only have made one HTTP call due to caching
        Http::assertSentCount(1);
    }

    public function test_get_access_token_throws_on_failure(): void
    {
        Http::fake([
            'zoom.us/oauth/token' => Http::response(['error' => 'unauthorized'], 401),
        ]);

        Cache::forget('zoom_s2s_access_token');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to obtain Zoom access token');

        $this->service->getAccessToken();
    }
}
