<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Models\WorkshopPage;
use App\Services\WorkshopContentDiffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkshopPageController extends Controller
{
    public function __construct(private WorkshopContentDiffService $diffService) {}

    public function index(Workshop $workshop): View
    {
        $pages = $workshop->pages;
        $pendingCount = $workshop->pendingPages()->count();

        return view('workshops.pages.index', compact('workshop', 'pages', 'pendingCount'));
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

        if ($workshop->allPageVersions()->where('slug', $validated['slug'])->exists()) {
            return back()->withInput()->with('error', 'A page with that slug already exists in this workshop.');
        }

        // First-time authoring publishes immediately - there's nothing live
        // yet to protect, so the review gate only applies to edits of
        // already-published content (see applyContentEdit()).
        $workshop->allPageVersions()->create([
            ...$validated,
            'sort_order' => $workshop->allPageVersions()->max('sort_order') + 1,
            'published_at' => now(),
            'published_by' => $request->user()->id,
        ]);

        return redirect()->route('workshops.pages.index', $workshop)->with('success', 'Page added.');
    }

    public function edit(Workshop $workshop, WorkshopPage $page): View
    {
        return view('workshops.pages.edit', compact('workshop', 'page'));
    }

    public function update(Request $request, Workshop $workshop, WorkshopPage $page): RedirectResponse
    {
        $validated = $this->validatePage($request);

        if ($validated['slug'] !== $page->slug
            && $workshop->allPageVersions()->where('slug', $validated['slug'])->where('id', '!=', $page->id)->exists()) {
            return back()->withInput()->with('error', 'A page with that slug already exists in this workshop.');
        }

        $current = $this->applyContentEdit($page, $validated);

        $message = $current->is($page)
            ? 'Page updated.'
            : "Saved as a draft (version {$current->version}) - review the diff and publish it to make it live. The current live version is untouched until then.";

        $route = $current->is($page)
            ? redirect()->route('workshops.pages.index', $workshop)
            : redirect()->route('workshops.pages.diff', [$workshop, $current]);

        return $route->with('success', $message);
    }

    /**
     * Editing content that's already live and published creates a new
     * DRAFT version instead of mutating the live one in place, so a
     * completed session's history never retroactively changes underneath
     * it and the change goes through review before anyone but staff sees
     * it. Content that's never been published yet (still being authored,
     * or an earlier draft that was itself never published) can be edited
     * freely in place - there's nothing live to protect.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function applyContentEdit(WorkshopPage $page, array $attributes): WorkshopPage
    {
        if (! $page->isPublished()) {
            $page->update($attributes);

            return $page;
        }

        $newVersion = $page->replicate(['created_at', 'updated_at']);
        $newVersion->fill($attributes);
        $newVersion->version = $page->version + 1;
        $newVersion->parent_workshop_page_id = $page->id;
        $newVersion->superseded_at = null;
        $newVersion->published_at = null;
        $newVersion->published_by = null;
        $newVersion->rejected_at = null;
        $newVersion->rejected_by = null;
        $newVersion->rejection_reason = null;
        $newVersion->save();

        return $newVersion;
    }

    /**
     * Makes a draft the live version: supersedes whatever was previously
     * live under the same slug (if anything), and marks this row published.
     * Also how first-time-authored pages effectively work via store().
     */
    public function publish(Workshop $workshop, WorkshopPage $page): RedirectResponse
    {
        abort_if($page->isPublished(), 422, 'This page is already live.');
        abort_if($page->isRejected(), 422, 'A rejected draft cannot be published - create a new edit instead.');

        $liveSibling = $workshop->allPageVersions()
            ->where('slug', $page->slug)
            ->whereNotNull('published_at')
            ->whereNull('superseded_at')
            ->where('id', '!=', $page->id)
            ->first();

        $liveSibling?->update(['superseded_at' => now()]);

        $page->update(['published_at' => now(), 'published_by' => auth()->id()]);

        return redirect()->route('workshops.pages.index', $workshop)->with('success', "\"{$page->title}\" is now live.");
    }

    public function reject(Request $request, Workshop $workshop, WorkshopPage $page): RedirectResponse
    {
        abort_if($page->isPublished(), 422, 'This page is already live and cannot be rejected.');

        $validated = $request->validate(['rejection_reason' => ['nullable', 'string', 'max:2000']]);

        $page->update([
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->route('workshops.review-queue', $workshop)->with('success', "Draft \"{$page->title}\" rejected.");
    }

    /**
     * Side-by-side review of a draft against the version it would replace -
     * a text diff of the body and a semantic diff of the checks.
     */
    public function diff(Workshop $workshop, WorkshopPage $page): View
    {
        $before = $page->parentPage;

        $textDiff = $this->diffService->diffText($before?->body, $page->body);
        $checksDiff = $this->diffService->diffKeyedArray($before?->checks() ?? [], $page->checks());

        $comments = $page->reviewComments()->with('author:id,name')->latest()->get();

        return view('workshops.pages.diff', compact('workshop', 'page', 'before', 'textDiff', 'checksDiff', 'comments'));
    }

    public function comment(Request $request, Workshop $workshop, WorkshopPage $page): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $page->reviewComments()->create([
            'body' => $validated['body'],
            'author_user_id' => $request->user()->id,
        ]);

        return redirect()->route('workshops.pages.diff', [$workshop, $page])->with('success', 'Comment added.');
    }

    /**
     * Every version of this page's slug, newest first, for an admin
     * reviewing what changed and when.
     */
    public function history(Workshop $workshop, WorkshopPage $page): View
    {
        $versions = $workshop->allPageVersions()
            ->where('slug', $page->slug)
            ->withCount(['checkAttempts', 'threads'])
            ->orderByDesc('version')
            ->get();

        return view('workshops.pages.history', compact('workshop', 'page', 'versions'));
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
