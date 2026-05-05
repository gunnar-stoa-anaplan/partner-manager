<?php

namespace Tests\Feature;

use App\Models\ContentAsset;
use Database\Seeders\ContentAssetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAssetSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_loads_apps_and_consensus_demos(): void
    {
        $this->seed(ContentAssetSeeder::class);

        // 27 apps in source — sales/pre_sales/delivery URL fields produce many rows.
        $this->assertGreaterThan(50, ContentAsset::count(), 'Expected meaningful number of app content assets');

        // 37 consensus demos
        $this->assertSame(37, ContentAsset::where('type', 'consensus_demo')->count());

        // Available URL → status available
        $this->assertGreaterThan(
            0,
            ContentAsset::where('status', 'available')->whereNotNull('url')->count()
        );
    }

    public function test_seeder_skips_n_a_fields(): void
    {
        $this->seed(ContentAssetSeeder::class);

        // "n/a" tokens in source should produce no row at all (not a not_available row).
        $this->assertSame(
            0,
            ContentAsset::where('notes', 'like', '%Source reference: n/a%')->count()
        );
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(ContentAssetSeeder::class);
        $first = ContentAsset::count();

        $this->seed(ContentAssetSeeder::class);
        $second = ContentAsset::count();

        $this->assertSame($first, $second, 'Re-seeding should not duplicate rows');
    }
}
