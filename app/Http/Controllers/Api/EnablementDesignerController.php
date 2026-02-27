<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\EnablementDesigner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DesignWorkshopRequest;
use Illuminate\Http\JsonResponse;

class EnablementDesignerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(DesignWorkshopRequest $request): JsonResponse
    {
        $prompt = $request->toPrompt();

        $response = EnablementDesigner::make()
            ->withPrefetchedContext($prompt)
            ->prompt($prompt);

        return response()->json([
            'workshop' => [
                'title' => $response['title'],
                'type' => $response['type'],
                'category' => $response['category'],
                'product_area' => $response['product_area'],
                'target_audience' => $response['target_audience'],
                'duration_hours' => $response['duration_hours'],
                'max_participants' => $response['max_participants'],
                'description' => $response['description'],
                'objectives' => $response['objectives'],
                'prerequisites' => $response['prerequisites'],
                'agenda' => $response['agenda'],
                'lab_design' => $response['lab_design'],
                'promotion_copy' => $response['promotion_copy'],
                'instructor_notes' => $response['instructor_notes'],
            ],
        ]);
    }
}
