<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

class DocumentationService
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'heading_permalink' => [
                'html_class' => 'docs-heading-anchor',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'insert' => 'after',
                'symbol' => '#',
                'aria_hidden' => true,
                'apply_id_to_heading' => true,
                'min_heading_level' => 2,
                'max_heading_level' => 3,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Get the rendered HTML content for a documentation page.
     */
    public function getRenderedContent(string $version, string $page): ?string
    {
        $filePath = $this->resolveMarkdownPath($version, $page);

        if ($filePath === null || ! file_exists($filePath)) {
            return null;
        }

        $cacheKey = "docs.{$version}.{$page}";
        $cacheTtl = (int) config('documentation.cache_ttl', 0);

        if ($cacheTtl > 0) {
            return Cache::remember($cacheKey, $cacheTtl * 60, function () use ($filePath, $version) {
                return $this->renderMarkdown($filePath, $version);
            });
        }

        return $this->renderMarkdown($filePath, $version);
    }

    /**
     * Extract H2/H3 headings from rendered HTML for the table of contents.
     *
     * @return array<int, array{level: int, id: string, text: string}>
     */
    public function extractTableOfContents(string $html): array
    {
        $toc = [];

        if (preg_match_all('/<h([23])\s+id="([^"]+)"[^>]*>(.*?)<\/h[23]>/s', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = strip_tags($match[3]);
                $text = trim(str_replace('#', '', $text));

                $toc[] = [
                    'level' => (int) $match[1],
                    'id' => $match[2],
                    'text' => $text,
                ];
            }
        }

        return $toc;
    }

    /**
     * Get the sidebar navigation structure from config.
     *
     * @return array<string, array<string, string>>
     */
    public function getNavigation(): array
    {
        return config('documentation.navigation', []);
    }

    /**
     * Get the display title for a page slug.
     */
    public function getPageTitle(string $slug): string
    {
        foreach (config('documentation.navigation', []) as $pages) {
            foreach ($pages as $title => $pageSlug) {
                if ($pageSlug === $slug) {
                    return $title;
                }
            }
        }

        return Str::title(str_replace('-', ' ', $slug));
    }

    /**
     * Resolve the absolute file path for a documentation image.
     */
    public function getImagePath(string $version, string $relativePath): ?string
    {
        $basePath = config('documentation.path')."/{$version}/images";
        $fullPath = realpath($basePath.'/'.$relativePath);

        if ($fullPath === false || ! str_starts_with($fullPath, realpath($basePath) ?: '')) {
            return null;
        }

        return $fullPath;
    }

    /**
     * Render a markdown file to HTML with post-processing.
     */
    private function renderMarkdown(string $filePath, string $version): string
    {
        $markdown = file_get_contents($filePath);
        $html = $this->converter->convert($markdown)->getContent();

        $html = $this->rewriteImagePaths($html, $version);
        $html = $this->transformCallouts($html);

        return $html;
    }

    /**
     * Rewrite relative image paths to use the docs image route.
     */
    private function rewriteImagePaths(string $html, string $version): string
    {
        return preg_replace_callback(
            '/src="images\/([^"]+)"/',
            function ($matches) use ($version) {
                $url = route('docs.image', ['version' => $version, 'path' => $matches[1]]);

                return 'src="'.$url.'"';
            },
            $html
        );
    }

    /**
     * Transform GitHub-style callout blockquotes into styled divs.
     *
     * Converts:
     *   <blockquote><p>[!NOTE]<br>Content</p></blockquote>
     * Into:
     *   <div class="docs-callout docs-callout-note"><p><strong>Note</strong> Content</p></div>
     */
    private function transformCallouts(string $html): string
    {
        $types = ['NOTE', 'WARNING', 'TIP', 'IMPORTANT'];
        $pattern = implode('|', $types);

        return preg_replace_callback(
            '#<blockquote>\s*<p>\[!('.$pattern.')\](?:<br\s*/?>|\n)?\s*(.*?)</p>(.*?)</blockquote>#s',
            function ($matches) {
                $type = strtolower($matches[1]);
                $label = ucfirst($type);
                $firstParagraph = trim($matches[2]);
                $rest = trim($matches[3]);

                $inner = "<p><strong>{$label}</strong> {$firstParagraph}</p>";
                if ($rest !== '') {
                    $inner .= $rest;
                }

                return "<div class=\"docs-callout docs-callout-{$type}\">{$inner}</div>";
            },
            $html
        );
    }

    /**
     * Build a search index for all pages in a given version.
     *
     * @return array<int, array{
     *     slug: string,
     *     title: string,
     *     section: string,
     *     headings: array<int, array{level: int, id: string, text: string}>,
     *     content: string
     * }>
     */
    public function buildSearchIndex(string $version): array
    {
        $cacheKey = "docs.search_index.{$version}";
        $cacheTtl = (int) config('documentation.cache_ttl', 0);

        $builder = function () use ($version): array {
            $index = [];

            foreach ($this->getNavigation() as $section => $pages) {
                foreach ($pages as $title => $slug) {
                    $filePath = $this->resolveMarkdownPath($version, $slug);

                    if ($filePath === null || ! file_exists($filePath)) {
                        continue;
                    }

                    $html = $this->renderMarkdown($filePath, $version);
                    $headings = $this->extractTableOfContents($html);
                    $plainText = $this->stripHtmlToPlainText($html);

                    $index[] = [
                        'slug' => $slug,
                        'title' => $title,
                        'section' => $section,
                        'headings' => $headings,
                        'content' => Str::limit($plainText, 2000, ''),
                    ];
                }
            }

            return $index;
        };

        if ($cacheTtl > 0) {
            return Cache::remember($cacheKey, $cacheTtl * 60, $builder);
        }

        return $builder();
    }

    /**
     * Strip HTML tags and normalize whitespace to produce searchable plain text.
     */
    private function stripHtmlToPlainText(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Resolve the full path to a markdown file.
     */
    private function resolveMarkdownPath(string $version, string $page): ?string
    {
        $safePage = preg_replace('/[^a-z0-9\-]/', '', $page);

        if ($safePage !== $page) {
            return null;
        }

        $path = config('documentation.path')."/{$version}/{$page}.md";

        return file_exists($path) ? $path : null;
    }
}
