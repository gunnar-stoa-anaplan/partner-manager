<?php

namespace Tests\Unit;

use App\Services\DocumentationService;
use Tests\TestCase;

class DocumentationServiceTest extends TestCase
{
    private DocumentationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DocumentationService;
    }

    public function test_renders_markdown_to_html(): void
    {
        $content = $this->service->getRenderedContent('1.0', 'overview');

        $this->assertNotNull($content);
        $this->assertStringContainsString('<h1>', $content);
        $this->assertStringContainsString('Partner Manager', $content);
    }

    public function test_returns_null_for_missing_page(): void
    {
        $content = $this->service->getRenderedContent('1.0', 'does-not-exist');

        $this->assertNull($content);
    }

    public function test_extracts_table_of_contents(): void
    {
        $content = $this->service->getRenderedContent('1.0', 'overview');
        $this->assertNotNull($content);

        $toc = $this->service->extractTableOfContents($content);

        $this->assertIsArray($toc);
        $this->assertNotEmpty($toc);

        $firstItem = $toc[0];
        $this->assertArrayHasKey('level', $firstItem);
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('text', $firstItem);
        $this->assertContains($firstItem['level'], [2, 3]);
    }

    public function test_transforms_note_callout(): void
    {
        $content = $this->service->getRenderedContent('1.0', 'overview');
        $this->assertNotNull($content);

        $this->assertStringContainsString('docs-callout docs-callout-note', $content);
        $this->assertStringContainsString('<strong>Note</strong>', $content);
    }

    public function test_transforms_tip_callout(): void
    {
        $content = $this->service->getRenderedContent('1.0', 'overview');
        $this->assertNotNull($content);

        $this->assertStringContainsString('docs-callout docs-callout-tip', $content);
        $this->assertStringContainsString('<strong>Tip</strong>', $content);
    }

    public function test_transforms_warning_callout(): void
    {
        $content = $this->service->getRenderedContent('1.0', 'getting-started');
        $this->assertNotNull($content);

        $this->assertStringContainsString('docs-callout docs-callout-warning', $content);
        $this->assertStringContainsString('<strong>Warning</strong>', $content);
    }

    public function test_get_navigation_returns_config_structure(): void
    {
        $navigation = $this->service->getNavigation();

        $this->assertIsArray($navigation);
        $this->assertArrayHasKey('Introduction', $navigation);
        $this->assertArrayHasKey('Plan', $navigation);
        $this->assertArrayHasKey('Engage', $navigation);
        $this->assertArrayHasKey('Measure', $navigation);
        $this->assertArrayHasKey('Act', $navigation);
    }

    public function test_get_page_title_returns_display_name(): void
    {
        $title = $this->service->getPageTitle('overview');
        $this->assertEquals('Overview', $title);

        $title = $this->service->getPageTitle('getting-started');
        $this->assertEquals('Getting Started', $title);

        $title = $this->service->getPageTitle('partner-paths');
        $this->assertEquals('Partner Paths', $title);
    }

    public function test_get_page_title_returns_titlecase_for_unknown_slug(): void
    {
        $title = $this->service->getPageTitle('unknown-page');

        $this->assertEquals('Unknown Page', $title);
    }

    public function test_get_image_path_prevents_traversal(): void
    {
        $path = $this->service->getImagePath('1.0', '../../etc/passwd');

        $this->assertNull($path);
    }

    public function test_get_image_path_returns_null_for_missing_file(): void
    {
        $path = $this->service->getImagePath('1.0', 'nonexistent.png');

        $this->assertNull($path);
    }

    public function test_headings_have_ids_for_anchor_links(): void
    {
        $content = $this->service->getRenderedContent('1.0', 'overview');
        $this->assertNotNull($content);

        $this->assertMatchesRegularExpression('/<h2\s+id="[^"]+"/', $content);
    }

    public function test_headings_have_permalink_anchors(): void
    {
        $content = $this->service->getRenderedContent('1.0', 'overview');
        $this->assertNotNull($content);

        $this->assertStringContainsString('docs-heading-anchor', $content);
    }

    public function test_build_search_index_returns_all_pages(): void
    {
        $index = $this->service->buildSearchIndex('1.0');

        $this->assertIsArray($index);
        $this->assertNotEmpty($index);

        $first = $index[0];
        $this->assertArrayHasKey('slug', $first);
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('section', $first);
        $this->assertArrayHasKey('headings', $first);
        $this->assertArrayHasKey('content', $first);
    }

    public function test_build_search_index_contains_known_pages(): void
    {
        $index = $this->service->buildSearchIndex('1.0');

        $slugs = array_column($index, 'slug');
        $this->assertContains('overview', $slugs);
        $this->assertContains('partner-paths', $slugs);
    }

    public function test_build_search_index_content_is_plain_text(): void
    {
        $index = $this->service->buildSearchIndex('1.0');

        $overviewEntry = collect($index)->firstWhere('slug', 'overview');
        $this->assertNotNull($overviewEntry);

        $this->assertDoesNotMatchRegularExpression('/<[^>]+>/', $overviewEntry['content']);
        $this->assertStringContainsString('Partner Manager', $overviewEntry['content']);
    }

    public function test_build_search_index_includes_headings(): void
    {
        $index = $this->service->buildSearchIndex('1.0');

        $overviewEntry = collect($index)->firstWhere('slug', 'overview');
        $this->assertNotNull($overviewEntry);
        $this->assertNotEmpty($overviewEntry['headings']);
    }

    public function test_build_search_index_section_matches_config(): void
    {
        $index = $this->service->buildSearchIndex('1.0');

        $overviewEntry = collect($index)->firstWhere('slug', 'overview');
        $this->assertNotNull($overviewEntry);
        $this->assertEquals('Introduction', $overviewEntry['section']);
    }
}
