<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IngestRequest;
use App\Services\DataIngestionService;
use Illuminate\Http\JsonResponse;

class IngestController extends Controller
{
    public function __construct(private DataIngestionService $ingestion) {}

    public function __invoke(IngestRequest $request, string $model): JsonResponse
    {
        $result = $this->ingestion->ingest(
            model: $model,
            rows: $request->validated('rows'),
            userId: $request->user()->id,
            connectionId: $request->validated('connection_id'),
            snapshotDate: $request->validated('snapshot_date'),
            source: 'api',
        );

        return response()->json([
            'message' => 'Ingest completed.',
            'model' => $model,
            'total' => $result->total,
            'created' => $result->created,
            'updated' => $result->updated,
            'failed' => $result->failed,
            'partners_created' => $result->partnersCreated,
            'import_id' => $result->importId,
            'errors' => $result->errors,
        ], $result->failed === $result->total ? 422 : 201);
    }
}
