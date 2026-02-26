<?php

namespace App\Http\Controllers;

use App\Http\Requests\DirectorySyncUploadRequest;
use App\Jobs\ProcessDirectorySync;
use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use Illuminate\View\View;

class DirectorySyncController extends Controller
{
    public function index(): View
    {
        $connections = IntegrationConnection::where('type', 'csv_upload')
            ->whereJsonContains('applied_templates', 'user-directory-import')
            ->get();

        $jobs = IntegrationJob::whereIn('connection_id', $connections->pluck('id'))
            ->latest()
            ->limit(25)
            ->get();

        return view('directory-sync.index', compact('connections', 'jobs'));
    }

    public function upload(DirectorySyncUploadRequest $request)
    {
        $connection = IntegrationConnection::findOrFail($request->connection_id);

        $filePath = $request->file('csv_file')->store('directory-sync', 'local');
        $fullPath = storage_path('app/'.$filePath);

        $job = IntegrationJob::create([
            'connection_id' => $connection->id,
            'direction' => 'import',
            'status' => 'pending',
        ]);

        ProcessDirectorySync::dispatch($connection, $fullPath, $job);

        return redirect()->route('directory-sync.index')
            ->with('success', 'Directory sync started. Users will be imported shortly.');
    }
}
