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

    // ─── Meeting CRUD ─────────────────────────────────────────

    /**
     * Create a Zoom meeting for a host user.
     *
     * @param  array{topic: string, type?: int, start_time?: string, duration?: int, timezone?: string, agenda?: string, settings?: array<string, mixed>}  $meetingData
     * @return array{id: string, join_url: string, start_url: string, password: string, uuid: string}
     */
    public function createMeeting(string $hostEmail, array $meetingData): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->retry(3, 1000, throw: false)
            ->post("{$this->baseUrl}/users/{$hostEmail}/meetings", $meetingData);

        if (! $response->successful()) {
            Log::error('Zoom meeting creation failed', [
                'host_email' => $hostEmail,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to create Zoom meeting: '.$response->body());
        }

        $data = $response->json();

        return [
            'id' => (string) $data['id'],
            'uuid' => $data['uuid'] ?? null,
            'join_url' => $data['join_url'],
            'start_url' => $data['start_url'],
            'password' => $data['password'] ?? null,
        ];
    }

    /**
     * Update an existing Zoom meeting.
     *
     * @param  array<string, mixed>  $meetingData
     */
    public function updateMeeting(string $meetingId, array $meetingData): bool
    {
        $response = Http::withToken($this->getAccessToken())
            ->retry(3, 1000, throw: false)
            ->patch("{$this->baseUrl}/meetings/{$meetingId}", $meetingData);

        if (! $response->successful()) {
            Log::warning('Zoom meeting update failed', [
                'meeting_id' => $meetingId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Delete (cancel) a Zoom meeting.
     */
    public function deleteMeeting(string $meetingId): bool
    {
        $response = Http::withToken($this->getAccessToken())
            ->retry(3, 1000, throw: false)
            ->delete("{$this->baseUrl}/meetings/{$meetingId}");

        if (! $response->successful()) {
            Log::warning('Zoom meeting deletion failed', [
                'meeting_id' => $meetingId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get meeting details from Zoom.
     *
     * @return array<string, mixed>|null
     */
    public function getMeeting(string $meetingId): ?array
    {
        $response = Http::withToken($this->getAccessToken())
            ->retry(3, 1000, throw: false)
            ->get("{$this->baseUrl}/meetings/{$meetingId}");

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    // ─── Reports & Recordings ─────────────────────────────────

    /**
     * Get participant report for a past meeting.
     * Available approximately 15 minutes after meeting ends.
     *
     * @return array<int, array{id: string, name: string, user_email: string, join_time: string, leave_time: string, duration: int, attentiveness_score: string|null}>
     */
    public function getMeetingParticipants(string $meetingId): array
    {
        $participants = [];
        $nextPageToken = '';

        do {
            $response = Http::withToken($this->getAccessToken())
                ->retry(3, 1000, throw: false)
                ->get("{$this->baseUrl}/report/meetings/{$meetingId}/participants", [
                    'page_size' => 300,
                    'next_page_token' => $nextPageToken,
                ]);

            if (! $response->successful()) {
                Log::warning('Zoom meeting participants report failed', [
                    'meeting_id' => $meetingId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $participants;
            }

            $data = $response->json();
            $participants = array_merge($participants, $data['participants'] ?? []);
            $nextPageToken = $data['next_page_token'] ?? '';
        } while ($nextPageToken !== '');

        return $participants;
    }

    /**
     * Get recording details for a meeting.
     *
     * @return array<string, mixed>
     */
    public function getMeetingRecordings(string $meetingId): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->retry(3, 1000, throw: false)
            ->get("{$this->baseUrl}/meetings/{$meetingId}/recordings");

        if (! $response->successful()) {
            return [];
        }

        return $response->json();
    }

    // ─── Meeting SDK ──────────────────────────────────────────

    /**
     * Generate a Meeting SDK signature (JWT) for joining/hosting a meeting.
     *
     * @param  int  $role  0 for participant, 1 for host
     */
    public function generateSdkSignature(string $meetingNumber, int $role): string
    {
        $sdkKey = config('services.zoom.sdk_key');
        $sdkSecret = config('services.zoom.sdk_secret');
        $iat = time();
        $exp = $iat + 7200;

        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'sdkKey' => $sdkKey,
            'mn' => $meetingNumber,
            'role' => $role,
            'iat' => $iat,
            'exp' => $exp,
            'tokenExp' => $exp,
        ]));

        $signature = hash_hmac('sha256', "{$header}.{$payload}", $sdkSecret, true);

        return "{$header}.{$payload}.".$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
