<?php

namespace App\Services\IdeaRoom;

use App\Exceptions\IdeaRoom\AiGatewayException;
use App\Services\IdeaRoom\Ai\OpenAiGateway;

class FrameDetectionService
{
    private const VISION_MODEL = 'gpt-4o';

    private const MAX_TOKENS = 2048;

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are a precise computer-vision assistant. Analyze the provided image and locate every distinct rectangular region that looks like a picture frame, artwork, mirror, window, screen, or empty frame-like placeholder.

CRITICAL: You must detect the ACTUAL frames visible in THIS specific image. Do NOT guess a generic grid or assume a symmetric layout. Count carefully — if there are 10 frames, return 10 entries. If there are 3, return 3.

Return ONLY a JSON object: {"frames": [{...}, ...]}
Each frame object must have exactly these numeric keys (all values are percentages 0-100 of the full image dimensions):
- "left": horizontal offset of the top-left corner
- "top": vertical offset of the top-left corner
- "width": width of the frame region
- "height": height of the frame region

Rules:
1. Examine the ENTIRE image — check corners, edges, and areas that may have smaller or partially hidden frames.
2. Each frame may have a DIFFERENT size and position — do not assume uniformity.
3. Coordinates are percentages (0-100) relative to the image width and height.
4. Round values to one decimal place.
5. Skip frames that are mostly occluded (less than 50% visible).
6. Order frames left-to-right, then top-to-bottom.
7. If no frames are found, return {"frames": []}.
8. Return ONLY valid JSON — no markdown, no commentary.
PROMPT;

    public function __construct(
        private readonly OpenAiGateway $gateway,
    ) {}

    /**
     * Detect frame positions in the given base64-encoded image.
     *
     * @param  string  $base64DataUrl  Full data URL (e.g., "data:image/jpeg;base64,...")
     * @return list<array{left: float, top: float, width: float, height: float}>
     *
     * @throws AiGatewayException
     */
    public function detect(string $base64DataUrl): array
    {
        $response = $this->gateway->chat([
            'model' => self::VISION_MODEL,
            'max_tokens' => self::MAX_TOKENS,
            'temperature' => 0.2,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'Analyze this gallery wall image and return the frame positions as JSON.'],
                        ['type' => 'image_url', 'image_url' => ['url' => $base64DataUrl, 'detail' => 'high']],
                    ],
                ],
            ],
        ]);

        $parsed = json_decode($response['content'], true);

        if (! is_array($parsed) || ! isset($parsed['frames'])) {
            return [];
        }

        return $this->sanitizeFrames($parsed['frames']);
    }

    /**
     * Validate and clamp frame values to the 0-100 range.
     *
     * @return list<array{left: float, top: float, width: float, height: float}>
     */
    private function sanitizeFrames(array $frames): array
    {
        $valid = [];

        foreach ($frames as $frame) {
            if (! isset($frame['left'], $frame['top'], $frame['width'], $frame['height'])) {
                continue;
            }

            $slot = [
                'left' => round(max(0, min(100, (float) $frame['left'])), 1),
                'top' => round(max(0, min(100, (float) $frame['top'])), 1),
                'width' => round(max(1, min(100, (float) $frame['width'])), 1),
                'height' => round(max(1, min(100, (float) $frame['height'])), 1),
            ];

            if ($slot['left'] + $slot['width'] > 100) {
                $slot['width'] = round(100 - $slot['left'], 1);
            }

            if ($slot['top'] + $slot['height'] > 100) {
                $slot['height'] = round(100 - $slot['top'], 1);
            }

            $valid[] = $slot;
        }

        return $valid;
    }
}
