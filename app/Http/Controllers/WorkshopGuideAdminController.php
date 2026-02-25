<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkshopGuideRequest;
use App\Http\Requests\UpdateWorkshopGuideRequest;
use App\Models\EnablementEvent;
use App\Models\WorkshopGuide;
use App\Services\WorkshopGuideService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkshopGuideAdminController extends Controller
{
    public function __construct(
        private WorkshopGuideService $guideService
    ) {}

    public function index(): View
    {
        $guides = WorkshopGuide::query()
            ->with('creator')
            ->withCount('pages')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.workshop-guides.index', compact('guides'));
    }

    public function create(): View
    {
        $events = EnablementEvent::published()
            ->doesntHave('workshopGuide')
            ->orderBy('title')
            ->get();

        return view('admin.workshop-guides.create', compact('events'));
    }

    public function store(StoreWorkshopGuideRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['navigation'] = json_decode($validated['navigation'], true) ?? [];

        $guide = WorkshopGuide::create($validated);

        return redirect()->route('admin.workshop-guides.show', $guide)
            ->with('success', 'Workshop guide created successfully.');
    }

    public function show(WorkshopGuide $workshopGuide): View
    {
        $workshopGuide->load(['pages', 'creator', 'enablementEvent']);
        $missingImages = $this->guideService->getMissingImages($workshopGuide);

        return view('admin.workshop-guides.show', [
            'guide' => $workshopGuide,
            'missingImages' => $missingImages,
        ]);
    }

    public function edit(WorkshopGuide $workshopGuide): View
    {
        $events = EnablementEvent::published()
            ->where(function ($q) use ($workshopGuide) {
                $q->doesntHave('workshopGuide')
                    ->orWhereHas('workshopGuide', fn ($q2) => $q2->where('id', $workshopGuide->id));
            })
            ->orderBy('title')
            ->get();

        return view('admin.workshop-guides.edit', [
            'guide' => $workshopGuide,
            'events' => $events,
        ]);
    }

    public function update(UpdateWorkshopGuideRequest $request, WorkshopGuide $workshopGuide): RedirectResponse
    {
        $validated = $request->validated();
        $validated['navigation'] = json_decode($validated['navigation'], true) ?? [];

        $workshopGuide->update($validated);

        return redirect()->route('admin.workshop-guides.show', $workshopGuide)
            ->with('success', 'Workshop guide updated successfully.');
    }

    public function destroy(WorkshopGuide $workshopGuide): RedirectResponse
    {
        if ($workshopGuide->isPublished()) {
            return back()->with('error', 'Published guides cannot be deleted. Archive it first.');
        }

        $this->guideService->deleteGuideContent($workshopGuide);
        $workshopGuide->delete();

        return redirect()->route('admin.workshop-guides.index')
            ->with('success', 'Workshop guide deleted successfully.');
    }

    public function publish(WorkshopGuide $workshopGuide): RedirectResponse
    {
        $workshopGuide->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Workshop guide published successfully.');
    }

    public function addPage(Request $request, WorkshopGuide $workshopGuide): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/'],
            'section' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $maxOrder = $workshopGuide->pages()->max('sort_order') ?? -1;

        $workshopGuide->pages()->create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'section' => $validated['section'],
            'sort_order' => $maxOrder + 1,
        ]);

        if (! empty($validated['content'])) {
            $this->guideService->storePageContent($workshopGuide, $validated['slug'], $validated['content']);
        }

        return back()->with('success', 'Page added successfully.');
    }

    public function uploadImage(Request $request, WorkshopGuide $workshopGuide): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $file = $request->file('image');
        $this->guideService->storeImage($workshopGuide, $file->getClientOriginalName(), $file->getContent());

        return back()->with('success', 'Image uploaded successfully.');
    }
}
