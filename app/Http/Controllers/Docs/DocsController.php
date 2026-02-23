<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Services\DocumentationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocsController extends Controller
{
    public function __construct(
        private DocumentationService $docs
    ) {}

    /**
     * Redirect to the default documentation version and page.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('docs.show', [
            'version' => config('documentation.default'),
            'page' => config('documentation.landing'),
        ]);
    }

    /**
     * Render a documentation page.
     */
    public function show(string $version, ?string $page = null): View
    {
        $page = $page ?? config('documentation.landing');

        abort_unless(in_array($version, config('documentation.versions')), 404);

        $content = $this->docs->getRenderedContent($version, $page);

        abort_if($content === null, 404);

        $toc = $this->docs->extractTableOfContents($content);
        $navigation = $this->docs->getNavigation();
        $versions = config('documentation.versions');
        $currentVersion = $version;
        $currentPage = $page;
        $pageTitle = $this->docs->getPageTitle($page);

        return view('docs.show', compact(
            'content',
            'toc',
            'navigation',
            'versions',
            'currentVersion',
            'currentPage',
            'pageTitle',
        ));
    }

    /**
     * Return the search index for a documentation version.
     */
    public function searchIndex(string $version): JsonResponse
    {
        abort_unless(in_array($version, config('documentation.versions')), 404);

        $index = $this->docs->buildSearchIndex($version);

        return response()->json($index, 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve a documentation image.
     */
    public function image(string $version, string $path): BinaryFileResponse
    {
        $filePath = $this->docs->getImagePath($version, $path);

        abort_unless($filePath !== null && file_exists($filePath), 404);

        return response()->file($filePath, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
