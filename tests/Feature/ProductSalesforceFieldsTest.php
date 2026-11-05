<?php

namespace Tests\Feature;

use App\Models\LaunchProgram;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSalesforceFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_a_product_with_salesforce_fields(): void
    {
        $product = Product::factory()->create();

        $this->assertStringStartsWith('01t', $product->salesforce_id);
        $this->assertNotEmpty($product->description);
    }

    public function test_products_from_different_salesforce_skus_can_share_a_name(): void
    {
        Product::factory()->create(['name' => 'Model Builder (s)', 'slug' => 'model-builder-s-2100', 'code' => '2100']);
        Product::factory()->create(['name' => 'Model Builder (s)', 'slug' => 'model-builder-s-2100p', 'code' => '2100P']);

        $this->assertSame(2, Product::query()->where('name', 'Model Builder (s)')->count());
    }

    public function test_salesforce_id_must_be_unique(): void
    {
        Product::factory()->create(['salesforce_id' => '01tPO00000ESIA4']);

        $this->expectException(QueryException::class);

        Product::factory()->create(['salesforce_id' => '01tPO00000ESIA4']);
    }

    public function test_salesforce_id_is_optional_for_products_created_in_the_app(): void
    {
        $product = Product::factory()->create(['salesforce_id' => null]);

        $this->assertNull($product->fresh()->salesforce_id);
    }

    public function test_published_scope_excludes_draft_salesforce_products(): void
    {
        $published = Product::factory()->create(['product_line' => 'SCM Apps', 'status' => 'published']);
        Product::factory()->create(['product_line' => 'Other/Platform', 'status' => 'draft']);

        $this->assertSame([$published->id], Product::published()->pluck('id')->all());
    }

    public function test_launch_program_keeps_working_when_its_product_is_removed(): void
    {
        $product = Product::factory()->create();
        $program = LaunchProgram::factory()->create(['product_id' => $product->id]);

        $product->delete();

        $this->assertNull($program->fresh()->product_id);
    }
}
