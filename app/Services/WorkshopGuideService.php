<?php

namespace App\Services;

use App\Models\WorkshopGuide;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

class WorkshopGuideService
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
     * Get the rendered HTML content for a workshop guide page.
     */
    public function getRenderedContent(WorkshopGuide $guide, string $pageSlug): ?string
    {
        $path = $guide->storagePath().'/'.$pageSlug.'.md';

        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        $markdown = Storage::disk('local')->get($path);
        $html = $this->converter->convert($markdown)->getContent();
        $html = $this->rewriteImagePaths($html, $guide);
        $html = $this->transformCallouts($html);

        return $html;
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
     * Store markdown content for a workshop guide page.
     */
    public function storePageContent(WorkshopGuide $guide, string $pageSlug, string $markdown): void
    {
        $path = $guide->storagePath().'/'.$pageSlug.'.md';
        Storage::disk('local')->put($path, $markdown);
    }

    /**
     * Store an image for a workshop guide.
     */
    public function storeImage(WorkshopGuide $guide, string $filename, string $contents): string
    {
        $path = $guide->storagePath().'/images/'.$filename;
        Storage::disk('local')->put($path, $contents);

        return $path;
    }

    /**
     * Get the absolute filesystem path for a guide image.
     */
    public function getImagePath(WorkshopGuide $guide, string $relativePath): ?string
    {
        $basePath = Storage::disk('local')->path($guide->storagePath().'/images');
        $fullPath = realpath($basePath.'/'.$relativePath);

        if ($fullPath === false || ! str_starts_with($fullPath, realpath($basePath) ?: '')) {
            return null;
        }

        return $fullPath;
    }

    /**
     * Detect image references in markdown that don't exist on disk.
     *
     * @return array<int, string>
     */
    public function getMissingImages(WorkshopGuide $guide): array
    {
        $missing = [];
        $imagesDir = $guide->storagePath().'/images';

        foreach ($guide->pages as $page) {
            $path = $guide->storagePath().'/'.$page->slug.'.md';

            if (! Storage::disk('local')->exists($path)) {
                continue;
            }

            $markdown = Storage::disk('local')->get($path);

            if (preg_match_all('/!\[.*?\]\(images\/([^)]+)\)/', $markdown, $matches)) {
                foreach ($matches[1] as $image) {
                    if (! Storage::disk('local')->exists($imagesDir.'/'.$image)) {
                        $missing[] = $image;
                    }
                }
            }
        }

        return array_unique($missing);
    }

    /**
     * Delete all stored content for a workshop guide.
     */
    public function deleteGuideContent(WorkshopGuide $guide): bool
    {
        return Storage::disk('local')->deleteDirectory($guide->storagePath());
    }

    /**
     * Build a search index for all pages in a guide.
     *
     * @return array<int, array{slug: string, title: string, section: string, headings: array, content: string}>
     */
    public function buildSearchIndex(WorkshopGuide $guide): array
    {
        $index = [];
        $navigation = $guide->navigation ?? [];

        foreach ($navigation as $section => $pages) {
            if (! is_array($pages)) {
                continue;
            }

            foreach ($pages as $title => $slug) {
                $html = $this->getRenderedContent($guide, $slug);

                if ($html === null) {
                    continue;
                }

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
    }

    /**
     * Rewrite relative image paths to use the workshop guide image route.
     */
    private function rewriteImagePaths(string $html, WorkshopGuide $guide): string
    {
        return preg_replace_callback(
            '/src="images\/([^"]+)"/',
            function ($matches) use ($guide) {
                $url = route('workshop-guides.image', ['guide' => $guide->slug, 'path' => $matches[1]]);

                return 'src="'.$url.'"';
            },
            $html
        );
    }

    /**
     * Transform GitHub-style callout blockquotes into styled divs.
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
     * Strip HTML tags and normalize whitespace to produce searchable plain text.
     */
    private function stripHtmlToPlainText(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
