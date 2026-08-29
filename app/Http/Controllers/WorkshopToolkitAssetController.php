<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopToolkitAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WorkshopToolkitAssetController extends Controller
{
    public function index(Workshop $workshop): View
    {
        $assets = $workshop->toolkitAssets()->latest()->get();

        return view('workshops.toolkit.index', compact('workshop', 'assets'));
    }

    public function store(Request $request, Workshop $workshop): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('file');
        $path = $file->store("workshop-toolkits/{$workshop->id}", 'local');

        $workshop->toolkitAssets()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('workshops.toolkit.index', $workshop)->with('success', 'Asset uploaded.');
    }

    public function download(Workshop $workshop, WorkshopToolkitAsset $asset): mixed
    {
        abort_unless($asset->workshop_id === $workshop->id, 404);

        return Storage::disk('local')->download($asset->file_path, $asset->file_name);
    }

    public function destroy(Workshop $workshop, WorkshopToolkitAsset $asset): RedirectResponse
    {
        abort_unless($asset->workshop_id === $workshop->id, 404);

        Storage::disk('local')->delete($asset->file_path);
        $asset->delete();

        return redirect()->route('workshops.toolkit.index', $workshop)->with('success', 'Asset removed.');
    }
}
