<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use App\Services\DataIngestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationHubController extends Controller
{
    public function __construct(private DataIngestionService $ingestion) {}

    public function connections(): JsonResponse
    {
        $connections = IntegrationConnection::query()
            ->where('status', 'active')
            ->withCount('jobs')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'direction' => $c->direction,
                'status' => $c->status,
                'jobs_count' => $c->jobs_count,
                'last_sync_at' => $c->last_sync_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $connections]);
    }

    public function pushData(Request $request, IntegrationConnection $connection): JsonResponse
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*' => ['required', 'array'],
            'target_model' => ['required', 'string', 'in:'.implode(',', DataIngestionService::allowedModels())],
            'snapshot_date' => ['nullable', 'date'],
        ]);

        $job = IntegrationJob::create([
            'connection_id' => $connection->id,
            'direction' => 'import',
            'status' => 'running',
            'total_rows' => count($validated['rows']),
            'started_at' => now(),
        ]);

        try {
            $result = $this->ingestion->ingest(
                model: $validated['target_model'],
                rows: $validated['rows'],
                userId: $request->user()->id,
                connectionId: $connection->id,
                snapshotDate: $validated['snapshot_date'] ?? null,
                source: 'integration_hub',
            );

            $job->update([
                'status' => $result->failed === $result->total ? 'failed' : 'completed',
                'rows_processed' => $result->created + $result->updated,
                'rows_failed' => $result->failed,
                'summary' => [
                    'created' => $result->created,
                    'updated' => $result->updated,
                    'partners_created' => $result->partnersCreated,
                    'import_id' => $result->importId,
                ],
                'error_log' => ! empty($result->errors) ? implode("\n", $result->errors) : null,
                'completed_at' => now(),
            ]);

            $connection->update(['last_sync_at' => now()]);
        } catch (\Throwable $e) {
            $job->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return response()->json([
                'job_id' => $job->id,
                'status' => 'failed',
                'message' => 'Processing failed: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
            'total_rows' => $job->total_rows,
            'rows_processed' => $job->rows_processed,
            'rows_failed' => $job->rows_failed,
            'summary' => $job->summary,
            'message' => 'Data processed.',
        ], $job->status === 'completed' ? 200 : 422);
    }

    public function pullData(Request $request, IntegrationConnection $connection): JsonResponse
    {
        $validated = $request->validate([
            'model' => ['required', 'string', 'in:partners,contacts,opportunities,resources,projects,leads'],
            'filters' => ['nullable', 'array'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $modelClass = $this->resolveModel($validated['model']);

        if (! $modelClass) {
            return response()->json(['error' => 'Invalid model.'], 400);
        }

        $query = $modelClass::query();

        if (! empty($validated['filters'])) {
            foreach ($validated['filters'] as $field => $value) {
                $query->where($field, $value);
            }
        }

        $limit = $validated['limit'] ?? 100;
        $data = $query->limit($limit)->get();

        $job = IntegrationJob::create([
            'connection_id' => $connection->id,
            'direction' => 'export',
            'status' => 'completed',
            'total_rows' => $data->count(),
            'rows_processed' => $data->count(),
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        return response()->json([
            'job_id' => $job->id,
            'model' => $validated['model'],
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    public function jobStatus(IntegrationJob $job): JsonResponse
    {
        return response()->json([
            'id' => $job->id,
            'connection_id' => $job->connection_id,
            'direction' => $job->direction,
            'status' => $job->status,
            'total_rows' => $job->total_rows,
            'rows_processed' => $job->rows_processed,
            'rows_failed' => $job->rows_failed,
            'success_rate' => $job->successRate(),
            'duration_seconds' => $job->durationSeconds(),
            'started_at' => $job->started_at?->toIso8601String(),
            'completed_at' => $job->completed_at?->toIso8601String(),
            'summary' => $job->summary,
            'error_log' => $job->error_log,
        ]);
    }

    private function resolveModel(string $name): ?string
    {
        return match ($name) {
            'partners' => \App\Models\Partner::class,
            'contacts' => \App\Models\Contact::class,
            'opportunities' => \App\Models\Opportunity::class,
            'resources' => \App\Models\Resource::class,
            'projects' => \App\Models\Project::class,
            'leads' => \App\Models\Lead::class,
            default => null,
        };
    }
}
