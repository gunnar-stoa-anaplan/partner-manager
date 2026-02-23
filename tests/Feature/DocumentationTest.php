<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use InteractsWithViews;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_docs_index_redirects_to_default_version(): void
    {
        $response = $this->get('/docs');

        $response->assertRedirect(route('docs.show', [
            'version' => config('documentation.default'),
            'page' => config('documentation.landing'),
        ]));
    }

    public function test_docs_show_renders_overview_page(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => 'overview']));

        $response->assertOk();
        $response->assertViewIs('docs.show');
        $response->assertViewHas('content');
        $response->assertViewHas('navigation');
        $response->assertViewHas('toc');
        $response->assertViewHas('currentVersion', '1.0');
        $response->assertViewHas('currentPage', 'overview');
        $response->assertViewHas('pageTitle', 'Overview');
    }

    public function test_docs_show_renders_getting_started_page(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => 'getting-started']));

        $response->assertOk();
        $response->assertViewHas('pageTitle', 'Getting Started');
    }

    public function test_docs_show_defaults_to_landing_page(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0']));

        $response->assertOk();
        $response->assertViewHas('currentPage', 'overview');
    }

    public function test_docs_show_returns_404_for_missing_page(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => 'nonexistent-page']));

        $response->assertNotFound();
    }

    public function test_docs_show_returns_404_for_invalid_version(): void
    {
        $response = $this->get(route('docs.show', ['version' => '99.0', 'page' => 'overview']));

        $response->assertNotFound();
    }

    public function test_docs_no_auth_required(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => 'overview']));

        $response->assertOk();
    }

    public function test_docs_content_contains_rendered_html(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => 'overview']));

        $response->assertOk();
        $response->assertSee('Partner Manager', false);
        $response->assertSee('<h1>', false);
    }

    public function test_docs_navigation_renders_sections(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => 'overview']));

        $response->assertOk();
        $response->assertSee('Introduction');
        $response->assertSee('Plan');
        $response->assertSee('Engage');
        $response->assertSee('Measure');
    }

    public function test_docs_toc_renders_headings(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => 'overview']));

        $response->assertOk();
        $response->assertSee('On This Page');
        $response->assertSee('Philosophy');
    }

    public function test_docs_callouts_are_transformed(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => 'overview']));

        $response->assertOk();
        $response->assertSee('docs-callout docs-callout-note', false);
        $response->assertSee('docs-callout docs-callout-tip', false);
    }

    public function test_docs_image_returns_404_for_missing_image(): void
    {
        $response = $this->get(route('docs.image', ['version' => '1.0', 'path' => 'missing.png']));

        $response->assertNotFound();
    }

    public function test_docs_image_prevents_path_traversal(): void
    {
        $response = $this->get('/docs/1.0/images/../../etc/passwd');

        $response->assertNotFound();
    }

    public function test_docs_page_slug_rejects_invalid_characters(): void
    {
        $response = $this->get(route('docs.show', ['version' => '1.0', 'page' => '../etc/passwd']));

        $response->assertNotFound();
    }

    public function test_search_index_returns_json(): void
    {
        $response = $this->get(route('docs.search-index', ['version' => '1.0']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_search_index_contains_expected_structure(): void
    {
        $response = $this->get(route('docs.search-index', ['version' => '1.0']));

        $response->assertOk();
        $response->assertJsonIsArray();
        $response->assertJsonStructure([
            '*' => ['slug', 'title', 'section', 'headings', 'content'],
        ]);
    }

    public function test_search_index_includes_known_pages(): void
    {
        $response = $this->get(route('docs.search-index', ['version' => '1.0']));

        $response->assertOk();
        $data = $response->json();
        $slugs = array_column($data, 'slug');

        $this->assertContains('overview', $slugs);
        $this->assertContains('getting-started', $slugs);
        $this->assertContains('partner-paths', $slugs);
    }

    public function test_search_index_returns_404_for_invalid_version(): void
    {
        $response = $this->get(route('docs.search-index', ['version' => '99.0']));

        $response->assertNotFound();
    }

    public function test_search_index_has_cache_control_header(): void
    {
        $response = $this->get(route('docs.search-index', ['version' => '1.0']));

        $response->assertOk();
        $response->assertHeader('Cache-Control');
    }

    public function test_search_index_no_auth_required(): void
    {
        $response = $this->get(route('docs.search-index', ['version' => '1.0']));

        $response->assertOk();
    }
}
