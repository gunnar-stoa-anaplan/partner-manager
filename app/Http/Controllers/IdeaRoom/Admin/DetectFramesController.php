<?php

namespace App\Http\Controllers\IdeaRoom\Admin;

use App\Exceptions\IdeaRoom\AiGatewayException;
use App\Http\Controllers\Controller;
use App\Services\IdeaRoom\FrameDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetectFramesController extends Controller
{
    public function __invoke(Request $request, FrameDetectionService $service): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'string', 'regex:/^data:image\/(jpeg|png|webp);base64,/'],
        ], [
            'image.required' => 'No image data provided.',
            'image.regex' => 'Image must be a base64-encoded JPEG, PNG, or WebP.',
        ]);

        $apiKey = config('idea_room.ai_providers.openai.api_key');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'ai_not_configured',
                'message' => 'Frame detection requires an OpenAI API key. Configure OPENAI_API_KEY in your environment.',
            ], 422);
        }

        try {
            $slots = $service->detect($request->input('image'));

            if (empty($slots)) {
                return response()->json([
                    'slots' => [],
                    'message' => 'No frames were detected in this image. You can add frames manually.',
                ]);
            }

            return response()->json([
                'slots' => $slots,
                'message' => count($slots).' frame(s) detected.',
            ]);
        } catch (AiGatewayException) {
            return response()->json([
                'error' => 'ai_unavailable',
                'message' => 'Frame detection is temporarily unavailable. Please try again or add frames manually.',
            ], 503);
        }
    }
}
