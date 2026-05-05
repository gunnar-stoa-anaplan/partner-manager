<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Journey;
use App\Models\JourneyStageItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyBuilderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $psm;

    private Journey $journey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->psm = User::factory()->psm()->create();
        $this->journey = Journey::factory()->create();
    }

    public function test_admin_can_view_builder(): void
    {
        Asset::factory()->count(3)->create(['status' => 'active', 'scope' => 'global']);

        $response = $this->actingAs($this->admin)->get(route('journeys.builder', $this->journey));

        $response->assertOk();
        $response->assertViewIs('journeys.builder');
        $response->assertViewHas('assets', fn ($a) => $a->count() === 3);
        $response->assertViewHas('stages', fn ($s) => $s->count() === 5);
    }

    public function test_unassigned_psm_cannot_view_builder(): void
    {
        $response = $this->actingAs($this->psm)->get(route('journeys.builder', $this->journey));

        $response->assertForbidden();
    }

    public function test_assigned_psm_can_view_builder(): void
    {
        $this->journey->assignments()->create(['user_id' => $this->psm->id]);

        $response = $this->actingAs($this->psm)->get(route('journeys.builder', $this->journey));

        $response->assertOk();
    }

    public function test_attach_asset_creates_stage_item(): void
    {
        $asset = Asset::factory()->create(['status' => 'active', 'scope' => 'global']);

        $response = $this->actingAs($this->admin)->postJson(
            route('journeys.builder.attach', $this->journey),
            ['stage_number' => 2, 'item_type' => 'asset', 'item_id' => $asset->id],
        );

        $response->assertOk();
        $response->assertJsonPath('asset_id', $asset->id);
        $response->assertJsonPath('stage_number', 2);

        $this->assertDatabaseHas('journey_stage_items', [
            'journey_id' => $this->journey->id,
            'stage_number' => 2,
            'item_type' => 'asset',
            'item_id' => $asset->id,
        ]);
    }

    public function test_attaching_same_asset_to_same_stage_is_idempotent(): void
    {
        $asset = Asset::factory()->create(['status' => 'active', 'scope' => 'global']);

        $first = $this->actingAs($this->admin)->postJson(
            route('journeys.builder.attach', $this->journey),
            ['stage_number' => 1, 'item_type' => 'asset', 'item_id' => $asset->id],
        );

        $second = $this->actingAs($this->admin)->postJson(
            route('journeys.builder.attach', $this->journey),
            ['stage_number' => 1, 'item_type' => 'asset', 'item_id' => $asset->id],
        );

        $first->assertOk();
        $second->assertOk();

        $this->assertSame(1, JourneyStageItem::query()->count());
    }

    public function test_attach_rejects_retired_asset(): void
    {
        $asset = Asset::factory()->create(['status' => 'retired', 'scope' => 'global']);

        $response = $this->actingAs($this->admin)->postJson(
            route('journeys.builder.attach', $this->journey),
            ['stage_number' => 1, 'item_type' => 'asset', 'item_id' => $asset->id],
        );

        $response->assertNotFound();
    }

    public function test_attach_rejects_other_journeys_scoped_asset(): void
    {
        $otherJourney = Journey::factory()->create();
        $asset = Asset::factory()->create([
            'status' => 'active',
            'scope' => 'journey:'.$otherJourney->id,
        ]);

        $response = $this->actingAs($this->admin)->postJson(
            route('journeys.builder.attach', $this->journey),
            ['stage_number' => 1, 'item_type' => 'asset', 'item_id' => $asset->id],
        );

        $response->assertNotFound();
    }

    public function test_journeys_own_scoped_asset_is_allowed(): void
    {
        $asset = Asset::factory()->create([
            'status' => 'active',
            'scope' => 'journey:'.$this->journey->id,
        ]);

        $response = $this->actingAs($this->admin)->postJson(
            route('journeys.builder.attach', $this->journey),
            ['stage_number' => 1, 'item_type' => 'asset', 'item_id' => $asset->id],
        );

        $response->assertOk();
    }

    public function test_detach_removes_item(): void
    {
        $asset = Asset::factory()->create(['status' => 'active', 'scope' => 'global']);
        $item = JourneyStageItem::query()->create([
            'journey_id' => $this->journey->id,
            'stage_number' => 1,
            'item_type' => 'asset',
            'item_id' => $asset->id,
            'position' => 0,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(
            route('journeys.builder.detach', ['journey' => $this->journey, 'item' => $item]),
        );

        $response->assertOk();
        $this->assertDatabaseMissing('journey_stage_items', ['id' => $item->id]);
    }

    public function test_detach_404s_for_item_in_different_journey(): void
    {
        $otherJourney = Journey::factory()->create();
        $asset = Asset::factory()->create(['status' => 'active', 'scope' => 'global']);
        $item = JourneyStageItem::query()->create([
            'journey_id' => $otherJourney->id,
            'stage_number' => 1,
            'item_type' => 'asset',
            'item_id' => $asset->id,
            'position' => 0,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(
            route('journeys.builder.detach', ['journey' => $this->journey, 'item' => $item]),
        );

        $response->assertNotFound();
    }

    public function test_attach_validates_stage_number(): void
    {
        $asset = Asset::factory()->create(['status' => 'active', 'scope' => 'global']);

        $response = $this->actingAs($this->admin)->postJson(
            route('journeys.builder.attach', $this->journey),
            ['stage_number' => 99, 'item_type' => 'asset', 'item_id' => $asset->id],
        );

        $response->assertStatus(422);
    }

    public function test_existing_items_render_in_builder_view(): void
    {
        $asset = Asset::factory()->create(['status' => 'active', 'scope' => 'global', 'name' => 'IFP Test Course']);
        JourneyStageItem::query()->create([
            'journey_id' => $this->journey->id,
            'stage_number' => 3,
            'item_type' => 'asset',
            'item_id' => $asset->id,
            'position' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('journeys.builder', $this->journey));

        $response->assertOk();
        $response->assertSee('IFP Test Course');
    }
}
