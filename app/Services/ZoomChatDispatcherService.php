<?php

namespace App\Services;

use App\Enums\ZoomChatMessageStatus;
use App\Models\ZoomChatMessage;
use App\Models\ZoomChatResponder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZoomChatDispatcherService
{
    private const MAX_CHAT_LENGTH = 4096;

    public function __construct(
        private ZoomApiService $zoomApi,
    ) {}

    /**
     * Process a single chat message through the configured agent.
     */
    public function processMessage(ZoomChatMessage $chatMessage): void
    {
        $responder = $chatMessage->responder;
        $startTime = microtime(true);

        $chatMessage->update(['status' => ZoomChatMessageStatus::Processing]);

        if (! $responder->is_active) {
            $chatMessage->update([
                'status' => ZoomChatMessageStatus::Skipped,
                'error_message' => 'Responder was deactivated before processing.',
            ]);

            return;
        }

        try {
            $cleanedMessage = $responder->stripTrigger($chatMessage->message_text);
            $agent = $responder->resolveAgent();

            $response = $agent
                ->withPrefetchedContext($cleanedMessage)
                ->prompt($cleanedMessage);

            $responseText = is_string($response) ? $response : (string) $response;
            $responseText = $this->truncateResponse($responseText, $responder);

            $formattedMessage = "[{$responder->display_name}] {$responseText}";

            $sent = $this->zoomApi->sendChatMessage(
                $responder->zoom_meeting_id,
                $formattedMessage
            );

            $elapsedMs = (int) ((microtime(true) - $startTime) * 1000);

            $chatMessage->update([
                'status' => $sent ? ZoomChatMessageStatus::Responded : ZoomChatMessageStatus::Failed,
                'response_text' => $responseText,
                'processing_time_ms' => $elapsedMs,
                'error_message' => $sent ? null : 'Failed to send message to Zoom.',
            ]);
        } catch (\Throwable $e) {
            $elapsedMs = (int) ((microtime(true) - $startTime) * 1000);

            Log::error('Zoom chat message processing failed', [
                'message_id' => $chatMessage->id,
                'error' => $e->getMessage(),
            ]);

            $chatMessage->update([
                'status' => ZoomChatMessageStatus::Failed,
                'error_message' => $e->getMessage(),
                'processing_time_ms' => $elapsedMs,
            ]);
        }
    }

    /**
     * Determine if the responder should handle this message.
     */
    public function shouldRespond(ZoomChatResponder $responder, string $messageText): bool
    {
        if (! $responder->is_active) {
            return false;
        }

        if (! $responder->matchesTrigger($messageText)) {
            return false;
        }

        return ! $this->isRateLimited($responder);
    }

    /**
     * Check if the responder has exceeded its rate limit.
     */
    private function isRateLimited(ZoomChatResponder $responder): bool
    {
        $key = "zoom-chat-rate:{$responder->id}";
        $limit = $responder->rate_limit_per_minute;

        $current = Cache::get($key, 0);

        if ($current >= $limit) {
            return true;
        }

        Cache::put($key, $current + 1, 60);

        return false;
    }

    /**
     * Truncate the response to fit within Zoom's message limit.
     */
    private function truncateResponse(string $text, ZoomChatResponder $responder): string
    {
        $maxLength = self::MAX_CHAT_LENGTH - mb_strlen($responder->display_name) - 3;

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength - 3).'...';
    }
}
