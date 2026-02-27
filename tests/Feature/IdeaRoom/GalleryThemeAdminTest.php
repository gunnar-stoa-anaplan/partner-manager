<?php

namespace Tests\Feature\IdeaRoom;

use App\Models\IdeaRoom\GalleryTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\WithActivationSetup;

class GalleryThemeAdminTest extends TestCase
{
    use RefreshDatabase, WithActivationSetup;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();

        $this->activateModuleForUser('idea_room', $this->admin);

        Storage::fake('public');
    }

    public function test_admin_can_list_themes(): void
    {
        GalleryTheme::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('idea_room.admin.themes.index'));

        $response->assertOk();
        $response->assertSee('Gallery Themes');
    }

    public function test_non_admin_cannot_access_themes(): void
    {
        $response = $this->actingAs($this->user)->get(route('idea_room.admin.themes.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('idea_room.admin.themes.create'));

        $response->assertOk();
        $response->assertSee('Create Theme');
    }

    public function test_admin_can_create_theme(): void
    {
        $response = $this->actingAs($this->admin)->post(route('idea_room.admin.themes.store'), [
            'name' => 'Modern Loft',
            'slug' => 'modern-loft',
            'description' => 'A sleek modern gallery.',
            'background_image' => UploadedFile::fake()->image('bg.jpg', 1920, 1080),
            'frame_slots' => json_encode([
                ['left' => 10, 'top' => 10, 'width' => 30, 'height' => 40],
                ['left' => 50, 'top' => 10, 'width' => 30, 'height' => 40],
            ]),
            'aspect_ratio' => '16 / 9',
            'greyscale' => '0',
            'is_published' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('gallery_themes', [
            'name' => 'Modern Loft',
            'slug' => 'modern-loft',
            'is_published' => true,
            'max_frames' => 2,
        ]);

        Storage::disk('public')->assertExists(
            GalleryTheme::where('slug', 'modern-loft')->first()->background_image_path
        );
    }

    public function test_create_theme_requires_background_image(): void
    {
        $response = $this->actingAs($this->admin)->post(route('idea_room.admin.themes.store'), [
            'name' => 'No Image',
            'slug' => 'no-image',
            'frame_slots' => json_encode([['left' => 10, 'top' => 10, 'width' => 30, 'height' => 40]]),
            'aspect_ratio' => '16 / 9',
        ]);

        $response->assertSessionHasErrors('background_image');
    }

    public function test_admin_can_update_theme(): void
    {
        $theme = GalleryTheme::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put(route('idea_room.admin.themes.update', $theme), [
            'name' => 'Updated Name',
            'slug' => $theme->slug,
            'frame_slots' => json_encode($theme->frame_slots),
            'aspect_ratio' => $theme->aspect_ratio,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('gallery_themes', ['id' => $theme->id, 'name' => 'Updated Name']);
    }

    public function test_admin_can_toggle_publish(): void
    {
        $theme = GalleryTheme::factory()->create([
            'created_by' => $this->admin->id,
            'is_published' => true,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->admin)->post(route('idea_room.admin.themes.toggle-publish', $theme));

        $response->assertRedirect();
        $this->assertFalse($theme->fresh()->is_published);
    }

    public function test_admin_can_set_default(): void
    {
        $theme1 = GalleryTheme::factory()->default()->create(['created_by' => $this->admin->id]);
        $theme2 = GalleryTheme::factory()->published()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->post(route('idea_room.admin.themes.set-default', $theme2));

        $response->assertRedirect();
        $this->assertTrue($theme2->fresh()->is_default);
        $this->assertFalse($theme1->fresh()->is_default);
    }

    public function test_admin_can_delete_non_default_theme(): void
    {
        $theme = GalleryTheme::factory()->create([
            'created_by' => $this->admin->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('idea_room.admin.themes.destroy', $theme));

        $response->assertRedirect();
        $this->assertSoftDeleted('gallery_themes', ['id' => $theme->id]);
    }

    public function test_cannot_delete_default_theme(): void
    {
        $theme = GalleryTheme::factory()->default()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete(route('idea_room.admin.themes.destroy', $theme));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('gallery_themes', ['id' => $theme->id, 'deleted_at' => null]);
    }

    public function test_slug_must_be_unique(): void
    {
        GalleryTheme::factory()->create(['created_by' => $this->admin->id, 'slug' => 'taken-slug']);

        $response = $this->actingAs($this->admin)->post(route('idea_room.admin.themes.store'), [
            'name' => 'Duplicate',
            'slug' => 'taken-slug',
            'background_image' => UploadedFile::fake()->image('bg.jpg', 1920, 1080),
            'frame_slots' => json_encode([['left' => 10, 'top' => 10, 'width' => 30, 'height' => 40]]),
            'aspect_ratio' => '16 / 9',
        ]);

        $response->assertSessionHasErrors('slug');
    }
}
