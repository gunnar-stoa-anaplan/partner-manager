<?php

namespace App\Http\Controllers\Zoom;

use App\Enums\ZoomChatMessageStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessZoomChatMessage;
use App\Models\ZoomChatMessage;
use App\Models\ZoomChatResponder;
use App\Services\ZoomApiService;
use App\Services\ZoomChatDispatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(
        Request $request,
        ZoomApiService $zoomApi,
        ZoomChatDispatcherService $dispatcher,
    ): JsonResponse {
        // Handle Zoom endpoint URL validation challenge
        if ($request->input('event') === 'endpoint.url_validation') {
            $plainToken = $request->input('payload.plainToken');

            return response()->json($zoomApi->handleUrlValidation($plainToken));
        }

        // Verify webhook signature
        $signature = $request->header('x-zm-signature', '');
        $timestamp = $request->header('x-zm-request-timestamp', '');

        if (! $zoomApi->verifyWebhookSignature($request->getContent(), $timestamp, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Only handle chat message events
        if ($request->input('event') !== 'meeting.chat_message_sent') {
            return response()->json(['status' => 'ignored']);
        }

        $payload = $request->input('payload.object', []);
        $meetingId = (string) ($payload['id'] ?? '');
        $chatMessage = $payload['chat_message'] ?? [];

        $messageId = $chatMessage['message_id'] ?? '';
        $messageText = $chatMessage['message'] ?? '';
        $senderName = $chatMessage['sender']['name'] ?? 'Unknown';
        $senderEmail = $chatMessage['sender']['email'] ?? null;

        if (! $meetingId || ! $messageId || ! $messageText) {
            return response()->json(['status' => 'incomplete_payload']);
        }

        // Find active responder for this meeting
        $responder = ZoomChatResponder::where('zoom_meeting_id', $meetingId)
            ->where('is_active', true)
            ->first();

        if (! $responder) {
            return response()->json(['status' => 'no_responder']);
        }

        // Prevent duplicate processing
        if (ZoomChatMessage::where('zoom_message_id', $messageId)->exists()) {
            return response()->json(['status' => 'duplicate']);
        }

        // Check if we should respond
        if (! $dispatcher->shouldRespond($responder, $messageText)) {
            ZoomChatMessage::create([
                'zoom_chat_responder_id' => $responder->id,
                'zoom_message_id' => $messageId,
                'sender_name' => $senderName,
                'sender_email' => $senderEmail,
                'message_text' => $messageText,
                'agent_class' => $responder->agent_class,
                'status' => ZoomChatMessageStatus::Skipped,
            ]);

            return response()->json(['status' => 'skipped']);
        }

        // Create message record and dispatch processing job
        $message = ZoomChatMessage::create([
            'zoom_chat_responder_id' => $responder->id,
            'zoom_message_id' => $messageId,
            'sender_name' => $senderName,
            'sender_email' => $senderEmail,
            'message_text' => $messageText,
            'agent_class' => $responder->agent_class,
            'status' => ZoomChatMessageStatus::Received,
        ]);

        ProcessZoomChatMessage::dispatch($message);

        return response()->json(['status' => 'queued']);
    }
}
