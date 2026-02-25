<?php

namespace Tests\Unit;

use App\Models\WorkshopGuide;
use App\Models\WorkshopGuidePage;
use App\Services\WorkshopGuideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkshopGuideServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkshopGuideService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->service = new WorkshopGuideService;
    }

    private function createGuideWithContent(): WorkshopGuide
    {
        $guide = WorkshopGuide::factory()->create();

        Storage::disk('local')->put(
            "workshop-guides/{$guide->slug}/overview.md",
            "# Workshop Overview\n\n## Getting Started\n\nWelcome to the workshop. This guide walks you through the setup.\n\n### Prerequisites\n\n- Anaplan workspace access\n- Browser with JavaScript enabled\n\n> [!NOTE]\n> Make sure you have admin rights.\n\n> [!TIP]\n> Bookmark this page for quick access.\n\n![Setup Screen](images/setup.png)"
        );

        WorkshopGuidePage::factory()->create([
            'workshop_guide_id' => $guide->id,
            'slug' => 'overview',
            'section' => 'Getting Started',
            'sort_order' => 0,
        ]);

        return $guide;
    }

    public function test_renders_markdown_to_html(): void
    {
        $guide = $this->createGuideWithContent();

        $html = $this->service->getRenderedContent($guide, 'overview');

        $this->assertNotNull($html);
        $this->assertStringContainsString('<h1>', $html);
        $this->assertStringContainsString('Workshop Overview', $html);
        $this->assertStringContainsString('<h2', $html);
    }

    public function test_returns_null_for_missing_page(): void
    {
        $guide = WorkshopGuide::factory()->create();

        $html = $this->service->getRenderedContent($guide, 'nonexistent');

        $this->assertNull($html);
    }

    public function test_extracts_table_of_contents(): void
    {
        $guide = $this->createGuideWithContent();
        $html = $this->service->getRenderedContent($guide, 'overview');

        $toc = $this->service->extractTableOfContents($html);

        $this->assertNotEmpty($toc);
        $this->assertEquals(2, $toc[0]['level']);
        $this->assertEquals('Getting Started', $toc[0]['text']);
        $this->assertEquals(3, $toc[1]['level']);
        $this->assertEquals('Prerequisites', $toc[1]['text']);
    }

    public function test_transforms_callout_blocks(): void
    {
        $guide = $this->createGuideWithContent();

        $html = $this->service->getRenderedContent($guide, 'overview');

        $this->assertStringContainsString('docs-callout docs-callout-note', $html);
        $this->assertStringContainsString('docs-callout docs-callout-tip', $html);
    }

    public function test_rewrites_image_paths(): void
    {
        $guide = $this->createGuideWithContent();

        $html = $this->service->getRenderedContent($guide, 'overview');

        $this->assertStringContainsString(route('workshop-guides.image', ['guide' => $guide->slug, 'path' => 'setup.png']), $html);
        $this->assertStringNotContainsString('src="images/setup.png"', $html);
    }

    public function test_stores_page_content(): void
    {
        $guide = WorkshopGuide::factory()->create();

        $this->service->storePageContent($guide, 'lab-1', "# Lab 1\n\nContent here.");

        Storage::disk('local')->assertExists("workshop-guides/{$guide->slug}/lab-1.md");
    }

    public function test_stores_image(): void
    {
        $guide = WorkshopGuide::factory()->create();

        $this->service->storeImage($guide, 'screenshot.png', 'fake-image-data');

        Storage::disk('local')->assertExists("workshop-guides/{$guide->slug}/images/screenshot.png");
    }

    public function test_detects_missing_images(): void
    {
        $guide = $this->createGuideWithContent();

        $missing = $this->service->getMissingImages($guide);

        $this->assertContains('setup.png', $missing);
    }

    public function test_no_missing_images_when_all_present(): void
    {
        $guide = $this->createGuideWithContent();
        Storage::disk('local')->put("workshop-guides/{$guide->slug}/images/setup.png", 'fake');

        $missing = $this->service->getMissingImages($guide);

        $this->assertEmpty($missing);
    }

    public function test_deletes_guide_content(): void
    {
        $guide = $this->createGuideWithContent();
        Storage::disk('local')->assertExists("workshop-guides/{$guide->slug}/overview.md");

        $this->service->deleteGuideContent($guide);

        Storage::disk('local')->assertMissing("workshop-guides/{$guide->slug}/overview.md");
    }

    public function test_builds_search_index(): void
    {
        $guide = $this->createGuideWithContent();
        $guide->update([
            'navigation' => [
                'Getting Started' => ['Workshop Overview' => 'overview'],
            ],
        ]);

        $index = $this->service->buildSearchIndex($guide);

        $this->assertCount(1, $index);
        $this->assertEquals('overview', $index[0]['slug']);
        $this->assertEquals('Workshop Overview', $index[0]['title']);
        $this->assertEquals('Getting Started', $index[0]['section']);
        $this->assertArrayHasKey('headings', $index[0]);
        $this->assertArrayHasKey('content', $index[0]);
    }

    public function test_image_path_prevents_traversal(): void
    {
        $guide = WorkshopGuide::factory()->create();

        $path = $this->service->getImagePath($guide, '../../etc/passwd');

        $this->assertNull($path);
    }
}
