<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZoomApiService
{
    public function __construct(
        private string $accountId,
        private string $clientId,
        private string $clientSecret,
        private string $webhookSecretToken,
        private string $baseUrl = 'https://api.zoom.us/v2',
    ) {}

    /**
     * Get a Server-to-Server OAuth access token (cached for 55 minutes).
     */
    public function getAccessToken(): string
    {
        return Cache::remember('zoom_s2s_access_token', 3300, function () {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post('https://zoom.us/oauth/token', [
                    'grant_type' => 'account_credentials',
                    'account_id' => $this->accountId,
                ]);

            if (! $response->successful()) {
                Log::error('Zoom OAuth token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Failed to obtain Zoom access token: '.$response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Send a chat message to a live Zoom meeting.
     */
    public function sendChatMessage(string $meetingId, string $message): bool
    {
        $response = Http::withToken($this->getAccessToken())
            ->retry(3, 1000, throw: false)
            ->post("{$this->baseUrl}/live_meetings/{$meetingId}/chat/messages", [
                'message' => $message,
            ]);

        if (! $response->successful()) {
            Log::warning('Zoom chat message send failed', [
                'meeting_id' => $meetingId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Verify a Zoom webhook signature using HMAC-SHA256.
     */
    public function verifyWebhookSignature(string $payload, string $timestamp, string $signature): bool
    {
        $message = "v0:{$timestamp}:{$payload}";
        $hash = hash_hmac('sha256', $message, $this->webhookSecretToken);
        $expected = "v0={$hash}";

        return hash_equals($expected, $signature);
    }

    /**
     * Handle Zoom's endpoint URL validation challenge.
     *
     * @return array{plainToken: string, encryptedToken: string}
     */
    public function handleUrlValidation(string $plainToken): array
    {
        $encryptedToken = hash_hmac('sha256', $plainToken, $this->webhookSecretToken);

        return [
            'plainToken' => $plainToken,
            'encryptedToken' => $encryptedToken,
        ];
    }
}
