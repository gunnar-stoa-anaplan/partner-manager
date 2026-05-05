<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ContentAssetResource;
use App\Models\ContentAsset;
use App\Models\JourneyStageAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentAssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ContentAsset::query();

        if ($request->filled('lob')) {
            $query->forLob((string) $request->string('lob'));
        }
        if ($request->filled('persona')) {
            $query->forPersona((string) $request->string('persona'));
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }
        if ($request->filled('application')) {
            $query->forApplication((string) $request->string('application'));
        }
        if ($request->filled('type')) {
            $query->where('type', (string) $request->string('type'));
        }

        $perPage = min((int) ($request->per_page ?? 50), 200);
        $assets = $query->orderBy('application')->orderBy('persona')->orderBy('type')->paginate($perPage);

        return ContentAssetResource::collection($assets)->response();
    }

    public function show(int $id): JsonResponse
    {
        $asset = ContentAsset::findOrFail($id);

        return response()->json([
            'data' => new ContentAssetResource($asset),
        ]);
    }

    public function byApplication(string $application): JsonResponse
    {
        $assets = ContentAsset::query()
            ->forApplication($application)
            ->orderBy('persona')
            ->orderBy('type')
            ->get();

        return ContentAssetResource::collection($assets)->response();
    }

    public function forJourneyStage(string $journeyKey, int $stage): JsonResponse
    {
        $stageAssets = JourneyStageAsset::query()
            ->where('journey_key', $journeyKey)
            ->where('stage', $stage)
            ->with('asset')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        $payload = $stageAssets
            ->filter(fn (JourneyStageAsset $jsa) => $jsa->asset !== null)
            ->map(fn (JourneyStageAsset $jsa) => [
                'required' => $jsa->required,
                'display_order' => $jsa->display_order,
                'asset' => new ContentAssetResource($jsa->asset),
            ])
            ->values();

        return response()->json([
            'data' => $payload,
            'meta' => [
                'journey_key' => $journeyKey,
                'stage' => $stage,
                'count' => $payload->count(),
            ],
        ]);
    }
}
