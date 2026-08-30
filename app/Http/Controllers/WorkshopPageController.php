<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkshopPageController extends Controller
{
    public function index(Workshop $workshop): View
    {
        $pages = $workshop->pages;

        return view('workshops.pages.index', compact('workshop', 'pages'));
    }

    public function show(Workshop $workshop, WorkshopPage $page): View
    {
        return view('workshops.pages.show', compact('workshop', 'page'));
    }

    public function create(Workshop $workshop): View
    {
        return view('workshops.pages.create', compact('workshop'));
    }

    public function store(Request $request, Workshop $workshop): RedirectResponse
    {
        $validated = $this->validatePage($request);

        $workshop->pages()->create([
            ...$validated,
            'sort_order' => $workshop->pages()->max('sort_order') + 1,
        ]);

        return redirect()->route('workshops.pages.index', $workshop)->with('success', 'Page added.');
    }

    public function edit(Workshop $workshop, WorkshopPage $page): View
    {
        return view('workshops.pages.edit', compact('workshop', 'page'));
    }

    public function update(Request $request, Workshop $workshop, WorkshopPage $page): RedirectResponse
    {
        $page->update($this->validatePage($request));

        return redirect()->route('workshops.pages.index', $workshop)->with('success', 'Page updated.');
    }

    public function destroy(Workshop $workshop, WorkshopPage $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('workshops.pages.index', $workshop)->with('success', 'Page deleted.');
    }

    public function reorder(Request $request, Workshop $workshop): RedirectResponse
    {
        $validated = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);

        foreach ($validated['order'] as $index => $pageId) {
            WorkshopPage::where('id', $pageId)->where('workshop_id', $workshop->id)->update(['sort_order' => $index]);
        }

        return redirect()->route('workshops.pages.index', $workshop)->with('success', 'Order updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePage(Request $request): array
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'title' => ['required', 'string', 'max:255'],
            'audience' => ['required', Rule::in(array_keys(WorkshopPage::AUDIENCES))],
            'body' => ['required', 'string'],
            'checks_json' => ['nullable', 'json'],
        ]);

        $validated['checks_json'] = $validated['checks_json'] ? json_decode($validated['checks_json'], true) : null;

        return $validated;
    }
}
