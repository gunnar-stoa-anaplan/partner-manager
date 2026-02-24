<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\LabDesigner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DesignLabRequest;
use Illuminate\Http\JsonResponse;

class LabDesignerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(DesignLabRequest $request): JsonResponse
    {
        $prompt = $request->toPrompt();

        $response = LabDesigner::make(
            workshopContext: $request->validated('workshop_context', ''),
        )
            ->withPrefetchedContext($prompt)
            ->prompt($prompt);

        $checkpoints = [];

        for ($i = 1; $i <= 6; $i++) {
            $title = $response["checkpoint_{$i}_title"] ?? null;

            if (! $title) {
                break;
            }

            $checkpoints[] = [
                'sort_order' => $i,
                'title' => $title,
                'description' => $response["checkpoint_{$i}_description"],
                'checkpoint_type' => $response["checkpoint_{$i}_type"],
                'points' => $response["checkpoint_{$i}_points"],
                'hint' => $response["checkpoint_{$i}_hint"],
                'is_required' => strtolower($response["checkpoint_{$i}_required"] ?? 'yes') === 'yes',
            ];
        }

        return response()->json([
            'lab' => [
                'name' => $response['name'],
                'description' => $response['description'],
                'instructions' => $response['instructions'],
                'tenant' => $response['tenant'],
                'duration_minutes' => $response['duration_minutes'],
                'checkpoints' => $checkpoints,
            ],
        ]);
    }
}
