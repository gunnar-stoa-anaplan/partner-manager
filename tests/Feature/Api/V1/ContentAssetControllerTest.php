<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContentAsset;
use App\Models\JourneyStageAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAssetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $readToken;

    private string $unauthorizedToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->readToken = $this->user->createToken('test', ['journey:read'])->plainTextToken;
        $this->unauthorizedToken = $this->user->createToken('test-no-abilities', ['other:scope'])->plainTextToken;
    }

    private function authed(): self
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->readToken);
    }

    public function test_index_requires_journey_read_ability(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->unauthorizedToken)
            ->getJson('/api/v1/assets')
            ->assertForbidden();
    }

    public function test_index_lists_paginated_assets(): void
    {
        ContentAsset::factory()->count(3)->create();

        $response = $this->authed()->getJson('/api/v1/assets');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'total']])
            ->assertJsonCount(3, 'data');
    }

    public function test_index_filters_by_lob_persona_status_application(): void
    {
        $target = ContentAsset::factory()->create([
            'lob' => 'Finance',
            'persona' => 'sales',
            'status' => 'available',
            'application' => 'IFP',
        ]);
        ContentAsset::factory()->create(['lob' => 'HR', 'persona' => 'sales']);
        ContentAsset::factory()->create(['lob' => 'Finance', 'persona' => 'delivery']);

        $response = $this->authed()->getJson('/api/v1/assets?lob=Finance&persona=sales&status=available&application=IFP');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($target->id, $response->json('data.0.id'));
    }

    public function test_for_persona_scope_includes_all_persona(): void
    {
        ContentAsset::factory()->create(['persona' => 'sales']);
        ContentAsset::factory()->create(['persona' => 'all']);
        ContentAsset::factory()->create(['persona' => 'delivery']);

        $response = $this->authed()->getJson('/api/v1/assets?persona=sales');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_show_returns_single_asset(): void
    {
        $asset = ContentAsset::factory()->create();

        $response = $this->authed()->getJson("/api/v1/assets/{$asset->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $asset->id)
            ->assertJsonPath('data.name', $asset->name);
    }

    public function test_show_404s_for_missing_asset(): void
    {
        $this->authed()->getJson('/api/v1/assets/9999')->assertNotFound();
    }

    public function test_by_application_returns_all_assets_for_app(): void
    {
        ContentAsset::factory()->count(2)->create(['application' => 'Territory and Quota Planning']);
        ContentAsset::factory()->create(['application' => 'IFP']);

        $response = $this->authed()->getJson('/api/v1/assets/app/'.rawurlencode('Territory and Quota Planning'));

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_for_journey_stage_returns_ordered_assigned_assets(): void
    {
        $a = ContentAsset::factory()->create(['name' => 'First']);
        $b = ContentAsset::factory()->create(['name' => 'Second']);
        $other = ContentAsset::factory()->create(['name' => 'Other stage']);

        JourneyStageAsset::create(['journey_key' => 'delivery-ifp', 'stage' => 1, 'asset_id' => $b->id, 'display_order' => 2, 'required' => true]);
        JourneyStageAsset::create(['journey_key' => 'delivery-ifp', 'stage' => 1, 'asset_id' => $a->id, 'display_order' => 1, 'required' => false]);
        JourneyStageAsset::create(['journey_key' => 'delivery-ifp', 'stage' => 2, 'asset_id' => $other->id]);

        $response = $this->authed()->getJson('/api/v1/journey-stage-assets/delivery-ifp/1');

        $response->assertOk()
            ->assertJsonPath('meta.journey_key', 'delivery-ifp')
            ->assertJsonPath('meta.stage', 1)
            ->assertJsonPath('meta.count', 2)
            ->assertJsonPath('data.0.asset.id', $a->id)
            ->assertJsonPath('data.0.required', false)
            ->assertJsonPath('data.1.asset.id', $b->id)
            ->assertJsonPath('data.1.required', true);
    }

    public function test_for_journey_stage_returns_empty_when_no_assignments(): void
    {
        $response = $this->authed()->getJson('/api/v1/journey-stage-assets/nonexistent-journey/1');

        $response->assertOk()
            ->assertJsonPath('meta.count', 0)
            ->assertJsonCount(0, 'data');
    }
}
