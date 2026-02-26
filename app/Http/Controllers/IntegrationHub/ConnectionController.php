<?php

namespace App\Http\Controllers\IntegrationHub;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntegrationHub\StoreConnectionRequest;
use App\Http\Requests\IntegrationHub\UpdateConnectionRequest;
use App\Models\IntegrationConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConnectionController extends Controller
{
    public function index(): View
    {
        $connections = IntegrationConnection::query()
            ->withCount('jobs')
            ->withCount(['jobs as successful_jobs_count' => fn ($q) => $q->where('status', 'completed')])
            ->latest()
            ->get();

        return view('integration-hub.connections.index', compact('connections'));
    }

    public function create(): View
    {
        return view('integration-hub.connections.create');
    }

    public function store(StoreConnectionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['config'])) {
            $data['config'] = json_decode($data['config'], true);
        }

        IntegrationConnection::create($data);

        return redirect()
            ->route('integration_hub.connections.index')
            ->with('success', 'Connection created successfully.');
    }

    public function show(IntegrationConnection $connection): View
    {
        $connection->load(['jobs' => fn ($q) => $q->latest()->limit(20), 'mappings']);

        return view('integration-hub.connections.show', compact('connection'));
    }

    public function edit(IntegrationConnection $connection): View
    {
        return view('integration-hub.connections.edit', compact('connection'));
    }

    public function update(UpdateConnectionRequest $request, IntegrationConnection $connection): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['config'])) {
            $data['config'] = json_decode($data['config'], true);
        }

        $connection->update($data);

        return redirect()
            ->route('integration_hub.connections.show', $connection)
            ->with('success', 'Connection updated.');
    }

    public function destroy(IntegrationConnection $connection): RedirectResponse
    {
        $connection->delete();

        return redirect()
            ->route('integration_hub.connections.index')
            ->with('success', 'Connection deleted.');
    }

    public function test(IntegrationConnection $connection): JsonResponse
    {
        $result = $connection->testConnection();

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'latency_ms' => $result['latency_ms'] ?? null,
        ]);
    }
}
