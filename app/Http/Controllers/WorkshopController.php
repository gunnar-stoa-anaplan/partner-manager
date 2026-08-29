<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    public function index(): View
    {
        $workshops = Workshop::withCount('sessions')
            ->with('creator:id,name')
            ->orderByDesc('updated_at')
            ->get();

        return view('workshops.index', compact('workshops'));
    }

    public function create(): View
    {
        return view('workshops.create');
    }

    public function store(StoreWorkshopRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        $workshop = Workshop::create($validated);

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Workshop created successfully.');
    }

    public function show(Workshop $workshop): View
    {
        $workshop->load(['sessions' => fn ($query) => $query->withCount('activeParticipants')->orderBy('start_date')]);

        return view('workshops.show', compact('workshop'));
    }

    public function edit(Workshop $workshop): View
    {
        return view('workshops.edit', compact('workshop'));
    }

    public function update(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $workshop->update($request->validated());

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Workshop updated successfully.');
    }

    public function destroy(Workshop $workshop): RedirectResponse
    {
        $workshop->delete();

        return redirect()->route('workshops.index')
            ->with('success', 'Workshop deleted successfully.');
    }
}
