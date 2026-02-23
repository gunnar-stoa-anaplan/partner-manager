<?php

namespace Tests\Feature\IdeaRoom;

use App\Models\IdeaRoom\GalleryTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class GalleryApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Feature::for(null)->activate('idea_room');

        $this->user = User::factory()->create();
    }

    public function test_templates_endpoint_returns_published_themes(): void
    {
        GalleryTheme::factory()->published()->create(['created_by' => $this->user->id, 'name' => 'Visible']);
        GalleryTheme::factory()->unpublished()->create(['created_by' => $this->user->id, 'name' => 'Hidden']);

        $response = $this->actingAs($this->user)->getJson('/api/v1/idea-room/gallery/templates');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('Visible', $data[0]['name']);
    }

    public function test_config_endpoint_returns_default_theme(): void
    {
        $theme = GalleryTheme::factory()->default()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/idea-room/gallery/config');

        $response->assertOk();
        $response->assertJsonFragment(['template' => $theme->slug]);
        $response->assertJsonStructure([
            'template', 'name', 'greyscale', 'aspect_ratio',
            'gallery_image_url', 'mode', 'frame_slots', 'background_image_url',
        ]);
    }

    public function test_config_endpoint_respects_user_preference(): void
    {
        GalleryTheme::factory()->default()->create(['created_by' => $this->user->id, 'slug' => 'default-one']);
        $preferred = GalleryTheme::factory()->published()->create(['created_by' => $this->user->id, 'slug' => 'my-pick']);

        $this->user->preferences = ['gallery_template' => 'my-pick'];
        $this->user->save();

        $response = $this->actingAs($this->user)->getJson('/api/v1/idea-room/gallery/config');

        $response->assertOk();
        $response->assertJsonFragment(['template' => 'my-pick']);
    }

    public function test_config_falls_back_to_default_when_preference_invalid(): void
    {
        $default = GalleryTheme::factory()->default()->create(['created_by' => $this->user->id]);

        $this->user->preferences = ['gallery_template' => 'nonexistent'];
        $this->user->save();

        $response = $this->actingAs($this->user)->getJson('/api/v1/idea-room/gallery/config');

        $response->assertOk();
        $response->assertJsonFragment(['template' => $default->slug]);
    }

    public function test_set_template_validates_slug(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/idea-room/gallery/template', [
            'slug' => 'nonexistent-slug',
        ]);

        $response->assertStatus(422);
    }

    public function test_set_template_updates_user_preference(): void
    {
        $theme = GalleryTheme::factory()->published()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/idea-room/gallery/template', [
            'slug' => $theme->slug,
        ]);

        $response->assertOk();
        $this->assertEquals($theme->slug, $this->user->fresh()->preferences['gallery_template']);
    }
}
