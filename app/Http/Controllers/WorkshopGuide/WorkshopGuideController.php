<?php

namespace App\Http\Controllers\WorkshopGuide;

use App\Http\Controllers\Controller;
use App\Models\WorkshopGuide;
use App\Services\WorkshopGuideService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkshopGuideController extends Controller
{
    public function __construct(
        private WorkshopGuideService $guideService
    ) {}

    /**
     * Display the catalog of published workshop guides.
     */
    public function index(): View
    {
        $guides = WorkshopGuide::query()
            ->published()
            ->with('creator')
            ->orderByDesc('published_at')
            ->get();

        return view('workshop-guides.index', compact('guides'));
    }

    /**
     * Display a workshop guide page.
     */
    public function show(WorkshopGuide $guide, ?string $page = null): View
    {
        abort_unless($guide->isPublished(), 404);

        if ($guide->requires_auth) {
            abort_unless(auth()->check(), 403);
        }

        $page = $page ?? $guide->firstPageSlug();

        abort_if($page === null, 404);

        $content = $this->guideService->getRenderedContent($guide, $page);

        abort_if($content === null, 404);

        $toc = $this->guideService->extractTableOfContents($content);
        $navigation = $guide->navigation ?? [];
        $currentPage = $page;
        $surrounding = $guide->surroundingPages($page);

        $pageTitle = $this->resolvePageTitle($navigation, $page);

        return view('workshop-guides.show', compact(
            'guide',
            'content',
            'toc',
            'navigation',
            'currentPage',
            'pageTitle',
            'surrounding',
        ));
    }

    /**
     * Return the search index for a workshop guide.
     */
    public function searchIndex(WorkshopGuide $guide): JsonResponse
    {
        abort_unless($guide->isPublished(), 404);

        $index = $this->guideService->buildSearchIndex($guide);

        return response()->json($index, 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve a workshop guide image.
     */
    public function image(WorkshopGuide $guide, string $path): BinaryFileResponse
    {
        abort_unless($guide->isPublished(), 404);

        $filePath = $this->guideService->getImagePath($guide, $path);

        abort_unless($filePath !== null && file_exists($filePath), 404);

        return response()->file($filePath, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Resolve the display title for a page slug from the navigation structure.
     */
    private function resolvePageTitle(array $navigation, string $slug): string
    {
        foreach ($navigation as $pages) {
            if (is_array($pages)) {
                foreach ($pages as $title => $pageSlug) {
                    if ($pageSlug === $slug) {
                        return $title;
                    }
                }
            }
        }

        return str_replace('-', ' ', ucfirst($slug));
    }
}
