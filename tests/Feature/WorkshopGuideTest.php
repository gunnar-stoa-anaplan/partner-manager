<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkshopGuide;
use App\Models\WorkshopGuidePage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkshopGuideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('local');
    }

    private function createPublishedGuide(): WorkshopGuide
    {
        $guide = WorkshopGuide::factory()->published()->create();

        Storage::disk('local')->put(
            "workshop-guides/{$guide->slug}/overview.md",
            "# Workshop Overview\n\n## Getting Started\n\nWelcome to the workshop.\n\n### Prerequisites\n\nNone required."
        );

        WorkshopGuidePage::factory()->create([
            'workshop_guide_id' => $guide->id,
            'title' => 'Workshop Overview',
            'slug' => 'overview',
            'section' => 'Getting Started',
            'sort_order' => 0,
        ]);

        return $guide;
    }

    public function test_workshop_index_loads_without_auth(): void
    {
        $response = $this->get('/workshops');

        $response->assertOk();
        $response->assertViewIs('workshop-guides.index');
    }

    public function test_workshop_index_shows_published_guides(): void
    {
        $published = WorkshopGuide::factory()->published()->create();
        $draft = WorkshopGuide::factory()->draft()->create();

        $response = $this->get('/workshops');

        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee($draft->title);
    }

    public function test_workshop_show_renders_published_guide(): void
    {
        $guide = $this->createPublishedGuide();

        $response = $this->get("/workshops/{$guide->slug}/overview");

        $response->assertOk();
        $response->assertViewIs('workshop-guides.show');
        $response->assertViewHas('guide');
        $response->assertViewHas('content');
        $response->assertViewHas('toc');
        $response->assertViewHas('navigation');
        $response->assertViewHas('currentPage', 'overview');
        $response->assertViewHas('surrounding');
    }

    public function test_workshop_show_returns_404_for_draft_guide(): void
    {
        $guide = WorkshopGuide::factory()->draft()->create();

        $response = $this->get("/workshops/{$guide->slug}/overview");

        $response->assertNotFound();
    }

    public function test_workshop_show_redirects_to_first_page_when_no_page_specified(): void
    {
        $guide = $this->createPublishedGuide();

        $response = $this->get("/workshops/{$guide->slug}");

        $response->assertOk();
    }

    public function test_workshop_show_returns_404_for_missing_page(): void
    {
        $guide = WorkshopGuide::factory()->published()->create();

        $response = $this->get("/workshops/{$guide->slug}/nonexistent-page");

        $response->assertNotFound();
    }

    public function test_workshop_requires_auth_blocks_unauthenticated(): void
    {
        $guide = WorkshopGuide::factory()->published()->requiresAuth()->create();

        Storage::disk('local')->put(
            "workshop-guides/{$guide->slug}/overview.md",
            "# Overview\n\nContent here."
        );

        $response = $this->get("/workshops/{$guide->slug}/overview");

        $response->assertForbidden();
    }

    public function test_workshop_requires_auth_allows_authenticated(): void
    {
        $guide = WorkshopGuide::factory()->published()->requiresAuth()->create();
        $user = User::factory()->create();

        Storage::disk('local')->put(
            "workshop-guides/{$guide->slug}/overview.md",
            "# Overview\n\nContent here."
        );

        $response = $this->actingAs($user)->get("/workshops/{$guide->slug}/overview");

        $response->assertOk();
    }

    public function test_workshop_content_contains_rendered_html(): void
    {
        $guide = $this->createPublishedGuide();

        $response = $this->get("/workshops/{$guide->slug}/overview");

        $response->assertOk();
        $response->assertSee('Workshop Overview', false);
        $response->assertSee('<h1>', false);
    }

    public function test_workshop_toc_extracts_headings(): void
    {
        $guide = $this->createPublishedGuide();

        $response = $this->get("/workshops/{$guide->slug}/overview");

        $response->assertOk();
        $response->assertSee('On This Page');
        $response->assertSee('Getting Started');
    }

    public function test_workshop_prev_next_navigation_renders(): void
    {
        $guide = WorkshopGuide::factory()->published()->create([
            'navigation' => [
                'Getting Started' => [
                    'Overview' => 'overview',
                    'Setup' => 'setup',
                ],
            ],
        ]);

        Storage::disk('local')->put("workshop-guides/{$guide->slug}/overview.md", "# Overview\n\nContent.");
        Storage::disk('local')->put("workshop-guides/{$guide->slug}/setup.md", "# Setup\n\nContent.");

        $response = $this->get("/workshops/{$guide->slug}/overview");

        $response->assertOk();
        $response->assertSee('Next', false);
        $response->assertSee('Setup', false);
    }

    public function test_workshop_search_index_returns_json(): void
    {
        $guide = $this->createPublishedGuide();

        $response = $this->get("/workshops/{$guide->slug}/search-index");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonIsArray();
    }

    public function test_workshop_search_index_contains_expected_structure(): void
    {
        $guide = $this->createPublishedGuide();

        $response = $this->get("/workshops/{$guide->slug}/search-index");

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['slug', 'title', 'section', 'headings', 'content'],
        ]);
    }

    public function test_workshop_image_returns_404_for_missing(): void
    {
        $guide = WorkshopGuide::factory()->published()->create();

        $response = $this->get("/workshops/{$guide->slug}/images/missing.png");

        $response->assertNotFound();
    }

    public function test_workshop_image_prevents_path_traversal(): void
    {
        $guide = WorkshopGuide::factory()->published()->create();

        $response = $this->get("/workshops/{$guide->slug}/images/../../etc/passwd");

        $response->assertNotFound();
    }
}
