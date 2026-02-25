<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkshopGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class WorkshopGuideAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('local');
        Feature::for(null)->activate('enablement_admin');

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_index_lists_guides(): void
    {
        $guide = WorkshopGuide::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.workshop-guides.index'));

        $response->assertOk();
        $response->assertSee($guide->title);
    }

    public function test_admin_create_shows_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.workshop-guides.create'));

        $response->assertOk();
        $response->assertSee('Create Workshop Guide');
    }

    public function test_admin_store_creates_guide(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.workshop-guides.store'), [
            'title' => 'SCM Foundation Workshop',
            'description' => 'A hands-on workshop covering SCM fundamentals.',
            'navigation' => json_encode(['Getting Started' => ['Overview' => 'overview']]),
            'product_area' => 'SCM',
            'status' => 'draft',
            'requires_auth' => false,
            'estimated_duration_hours' => 8,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workshop_guides', [
            'title' => 'SCM Foundation Workshop',
            'product_area' => 'SCM',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.workshop-guides.store'), [
            'title' => '',
            'description' => '',
            'navigation' => '',
            'status' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'description', 'navigation', 'status']);
    }

    public function test_admin_show_displays_guide_details(): void
    {
        $guide = WorkshopGuide::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.workshop-guides.show', $guide));

        $response->assertOk();
        $response->assertSee($guide->title);
    }

    public function test_admin_edit_shows_form(): void
    {
        $guide = WorkshopGuide::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.workshop-guides.edit', $guide));

        $response->assertOk();
        $response->assertSee($guide->title);
    }

    public function test_admin_update_modifies_guide(): void
    {
        $guide = WorkshopGuide::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put(route('admin.workshop-guides.update', $guide), [
            'title' => 'Updated Workshop Title',
            'description' => 'Updated description.',
            'navigation' => json_encode($guide->navigation),
            'product_area' => 'IFP',
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workshop_guides', [
            'id' => $guide->id,
            'title' => 'Updated Workshop Title',
            'product_area' => 'IFP',
        ]);
    }

    public function test_admin_destroy_deletes_draft_guide(): void
    {
        $guide = WorkshopGuide::factory()->draft()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.workshop-guides.destroy', $guide));

        $response->assertRedirect(route('admin.workshop-guides.index'));
        $this->assertSoftDeleted('workshop_guides', ['id' => $guide->id]);
    }

    public function test_admin_destroy_rejects_published_guide(): void
    {
        $guide = WorkshopGuide::factory()->published()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.workshop-guides.destroy', $guide));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('workshop_guides', ['id' => $guide->id, 'deleted_at' => null]);
    }

    public function test_admin_publish_updates_status(): void
    {
        $guide = WorkshopGuide::factory()->draft()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.workshop-guides.publish', $guide));

        $response->assertRedirect();
        $this->assertDatabaseHas('workshop_guides', [
            'id' => $guide->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_add_page_creates_page_and_stores_content(): void
    {
        $guide = WorkshopGuide::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.workshop-guides.pages.store', $guide), [
            'title' => 'Lab 1: Foundation',
            'slug' => 'lab-1-foundation',
            'section' => 'Labs',
            'content' => "# Lab 1: Foundation\n\nThis is the first lab.",
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workshop_guide_pages', [
            'workshop_guide_id' => $guide->id,
            'title' => 'Lab 1: Foundation',
            'slug' => 'lab-1-foundation',
            'section' => 'Labs',
        ]);
        Storage::disk('local')->assertExists("workshop-guides/{$guide->slug}/lab-1-foundation.md");
    }

    public function test_admin_upload_image_stores_file(): void
    {
        $guide = WorkshopGuide::factory()->create(['created_by' => $this->admin->id]);
        $file = UploadedFile::fake()->image('screenshot.png', 800, 600);

        $response = $this->actingAs($this->admin)->post(route('admin.workshop-guides.images.store', $guide), [
            'image' => $file,
        ]);

        $response->assertRedirect();
        Storage::disk('local')->assertExists("workshop-guides/{$guide->slug}/images/screenshot.png");
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $psm = User::factory()->create(['role' => 'psm']);
        $guide = WorkshopGuide::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($psm)->get(route('admin.workshop-guides.index'));

        $response->assertForbidden();
    }
}
