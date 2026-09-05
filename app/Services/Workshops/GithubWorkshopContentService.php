<?php

namespace App\Services\Workshops;

use App\Models\Workshop;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use RuntimeException;

/**
 * Reads workshop content straight from the git repo the team authors it in
 * (branch -> PR -> merge to main), rather than hosting an editor in the app.
 * Read-only; publishing a change happens entirely on GitHub.
 *
 * Salvaged from the partner-success Laravel 13 spike. This is an ALTERNATIVE to
 * partner-manager's in-app content model (WorkshopContentDiffService + the
 * review/audit pipeline) — use it for workshops whose content lives in GitHub.
 *
 * Config (config/services.php › github): token, workshop_content_repo,
 * workshop_content_base. Content path for a workshop is
 *   "{workshop_content_base}/{workshop->slug}"
 * (override by giving Workshop a contentPath() method — used if present).
 *
 * Link rewriting targets the routes `workshops.content.show` / `.asset`; they
 * are only used if registered (Route::has), so this service is safe to ship
 * before those routes exist. Wire a controller + those routes to serve pages.
 */
class GithubWorkshopContentService
{
    private const MIME_TYPES = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
    ];

    public function __construct(
        private readonly ?string $token = null,
        private readonly ?string $repo = null,
    ) {}

    /**
     * The commit SHA on the repo's main branch — stamp this onto a participant
     * when they complete a workshop so you always know what they saw, even
     * after the content moves on.
     */
    public function currentSha(): string
    {
        return Cache::remember('workshop-content-main-sha:'.$this->repo(), now()->addMinutes(5), function () {
            return $this->api()->get("repos/{$this->repo()}/commits/main")->throw()->json('sha');
        });
    }

    /**
     * @return array<int, array{filename: string, title: string}>
     */
    public function listPages(Workshop $workshop): array
    {
        $response = $this->api()->get("repos/{$this->repo()}/contents/{$this->contentPath($workshop)}");

        if ($response->notFound()) {
            return [];
        }

        $response->throw();

        return collect($response->json())
            ->filter(fn (array $item) => $item['type'] === 'file'
                && str_ends_with($item['name'], '.html')
                && preg_match('/^\d/', $item['name']))
            ->sortBy('name', SORT_NATURAL)
            ->values()
            ->map(fn (array $file) => [
                'filename' => $file['name'],
                'title' => $this->extractTitle($workshop, $file['name']),
            ])->all();
    }

    /**
     * The rewritten HTML fragment for one page, safe to render with {!! !!} —
     * authored by trusted staff via a PR-reviewed git repo, not user input.
     */
    public function getPage(Workshop $workshop, string $filename): string
    {
        $html = $this->fetchFile("{$this->contentPath($workshop)}/{$filename}");

        return $this->rewriteFragment($html, $workshop, $this->contentPath($workshop));
    }

    /**
     * @return array{content: string, mime: string}
     */
    public function getAsset(string $relativePath): array
    {
        $content = $this->fetchFile($relativePath);
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        return [
            'content' => $content,
            'mime' => self::MIME_TYPES[$extension] ?? 'application/octet-stream',
        ];
    }

    private function contentPath(Workshop $workshop): string
    {
        if (method_exists($workshop, 'contentPath')) {
            return $workshop->contentPath();
        }

        $base = trim((string) config('services.github.workshop_content_base', 'workshops'), '/');

        return $base === '' ? $workshop->slug : "{$base}/{$workshop->slug}";
    }

    private function extractTitle(Workshop $workshop, string $filename): string
    {
        $html = $this->fetchFile("{$this->contentPath($workshop)}/{$filename}");

        if (preg_match('/<title>(.*?)<\/title>/si', $html, $matches)) {
            $title = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);

            // Titles separate page name from workshop name with an em dash or a
            // spaced hyphen (inconsistently) — split on either, but only a
            // hyphen with surrounding spaces so hyphenated words survive.
            return trim(preg_split('/\s+[—-]\s+/u', $title)[0]);
        }

        return $filename;
    }

    private function rewriteFragment(string $html, Workshop $workshop, string $pageDir): string
    {
        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);
        $nodes = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " main-content ")]');

        if ($nodes === false || $nodes->length === 0) {
            return '';
        }

        /** @var DOMElement $main */
        $main = $nodes->item(0);

        $hasAssetRoute = Route::has('workshops.content.asset');
        $hasPageRoute = Route::has('workshops.content.show');

        if ($hasAssetRoute) {
            foreach (iterator_to_array($xpath->query('.//img', $main)) as $img) {
                /** @var DOMElement $img */
                $src = $img->getAttribute('src');
                if ($src !== '' && ! str_starts_with($src, 'http')) {
                    $img->setAttribute('src', route('workshops.content.asset', [
                        'workshop' => $workshop,
                        'path' => $this->resolveRelativePath($pageDir, $src),
                    ]));
                }
            }
        }

        if ($hasPageRoute) {
            foreach (iterator_to_array($xpath->query('.//a[@href]', $main)) as $link) {
                /** @var DOMElement $link */
                $href = $link->getAttribute('href');
                if ($href !== '' && ! preg_match('/^(https?:|mailto:|#)/i', $href) && str_ends_with($href, '.html')) {
                    $link->setAttribute('href', route('workshops.content.show', [
                        'workshop' => $workshop,
                        'page' => basename($href),
                    ]));
                }
            }
        }

        $innerHtml = '';
        foreach ($main->childNodes as $child) {
            $innerHtml .= $document->saveHTML($child);
        }

        return $innerHtml;
    }

    /**
     * Resolve a relative asset/link reference (e.g. "../../img/x.png") against
     * the page's directory into a repo-root-relative path (e.g. "img/x.png").
     */
    private function resolveRelativePath(string $fromDir, string $relative): string
    {
        $parts = array_filter(explode('/', $fromDir));

        foreach (explode('/', $relative) as $segment) {
            if ($segment === '.' || $segment === '') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);

                continue;
            }
            $parts[] = $segment;
        }

        return implode('/', $parts);
    }

    private function fetchFile(string $repoPath): string
    {
        $response = $this->api()->get("repos/{$this->repo()}/contents/{$repoPath}")->throw();

        return base64_decode($response->json('content'));
    }

    private function api(): PendingRequest
    {
        $token = $this->token ?? config('services.github.token');

        if (! $token) {
            throw new RuntimeException('services.github.token (GITHUB_TOKEN) is not configured.');
        }

        return Http::withToken($token)->acceptJson()->baseUrl('https://api.github.com');
    }

    private function repo(): string
    {
        $repo = $this->repo ?? config('services.github.workshop_content_repo');

        if (! $repo) {
            throw new RuntimeException('services.github.workshop_content_repo (WORKSHOP_CONTENT_REPO) is not configured.');
        }

        return $repo;
    }
}
