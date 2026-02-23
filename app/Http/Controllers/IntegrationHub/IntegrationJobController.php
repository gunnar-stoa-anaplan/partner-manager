<?php

namespace App\Http\Controllers\IntegrationHub;

use App\Http\Controllers\Controller;
use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationJobController extends Controller
{
    public function index(Request $request): View
    {
        $query = IntegrationJob::query()->with('connection');

        if ($request->filled('direction')) {
            $query->where('direction', $request->input('direction'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('connection_id')) {
            $query->where('connection_id', $request->input('connection_id'));
        }

        $jobs = $query->latest()->paginate(25);
        $connections = IntegrationConnection::all();

        return view('integration-hub.jobs.index', compact('jobs', 'connections'));
    }

    public function show(IntegrationJob $job): View
    {
        $job->load('connection');

        return view('integration-hub.jobs.show', compact('job'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'connection_id' => ['required', 'exists:integration_connections,id'],
            'direction' => ['required', 'in:import,export'],
        ]);

        $connection = IntegrationConnection::findOrFail($validated['connection_id']);

        $job = IntegrationJob::create([
            'connection_id' => $connection->id,
            'direction' => $validated['direction'],
            'status' => 'pending',
            'started_at' => now(),
        ]);

        // TODO: Dispatch actual job processing via queue
        // dispatch(new ProcessIntegrationJob($job));

        return redirect()
            ->route('integration-hub.jobs.show', $job)
            ->with('success', 'Job queued successfully.');
    }

    public function retry(IntegrationJob $job): RedirectResponse
    {
        if ($job->status !== 'failed') {
            return redirect()
                ->route('integration-hub.jobs.show', $job)
                ->with('error', 'Only failed jobs can be retried.');
        }

        $newJob = IntegrationJob::create([
            'connection_id' => $job->connection_id,
            'direction' => $job->direction,
            'status' => 'pending',
            'started_at' => now(),
        ]);

        // TODO: Dispatch actual job processing via queue
        // dispatch(new ProcessIntegrationJob($newJob));

        return redirect()
            ->route('integration-hub.jobs.show', $newJob)
            ->with('success', 'Retry job queued.');
    }

    public function destroy(IntegrationJob $job): RedirectResponse
    {
        $job->delete();

        return redirect()
            ->route('integration-hub.jobs.index')
            ->with('success', 'Job record deleted.');
    }
}
