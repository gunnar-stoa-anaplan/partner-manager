<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\PsmObjective;
use App\Models\User;
use App\Services\PsmTargetImport\PsmTargetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PsmTargetImporterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a workbook shaped like the real one: row 1 metadata, row 2 headers
     * (A blank = partner), rows 3+ partner data.
     *
     * @param  array<int, array<int, string|int>>  $dataRows
     * @param  array<int, string>  $capabilities
     */
    private function makeWorkbook(array $capabilities, array $dataRows): string
    {
        $ss = new Spreadsheet;
        $sheet = $ss->getActiveSheet();
        $sheet->fromArray(['Q3 FY27', 'All Geos'], null, 'A1');
        $sheet->fromArray(array_merge([''], $capabilities), null, 'A2');
        $row = 3;
        foreach ($dataRows as $r) {
            $sheet->fromArray($r, null, 'A'.$row++);
        }
        $path = tempnam(sys_get_temp_dir(), 'psmimport_').'.xlsx';
        (new Xlsx($ss))->save($path);

        return $path;
    }

    public function test_plans_creates_matches_partners_and_skips_zero_and_unmatched(): void
    {
        $psm = User::factory()->psm()->create();
        Partner::factory()->create(['name' => 'Deloitte LLP', 'user_id' => $psm->id]);

        $file = $this->makeWorkbook(
            ['IFP Delivery Target', 'OWP Delivery Target'],
            [
                ['Deloitte LLP', 150, 0],       // 150 create, 0 skipped
                ['Ghost Partner Inc', 5, 5],    // unmatched → skipped entirely
            ],
        );

        $plan = app(PsmTargetImporter::class)->plan($file, 'delivery', 'Q3-FY27');

        $this->assertSame(1, $plan['summary']['partners_matched']);
        $this->assertSame(1, $plan['summary']['create']);      // only IFP 150 (OWP 0 not created)
        $this->assertContains('Ghost Partner Inc', $plan['unmatched_partners']);
        @unlink($file);
    }

    public function test_apply_creates_scoped_objective_and_reimport_updates_idempotently(): void
    {
        $psm = User::factory()->psm()->create();
        $partner = Partner::factory()->create(['name' => 'Deloitte LLP', 'user_id' => $psm->id]);

        $file = $this->makeWorkbook(['IFP Delivery Target'], [['Deloitte LLP', 150]]);
        $importer = app(PsmTargetImporter::class);

        $importer->apply($importer->plan($file, 'delivery', 'Q3-FY27'));

        $obj = PsmObjective::withoutGlobalScopes()->where('partner_id', $partner->id)->first();
        $this->assertNotNull($obj);
        $this->assertEquals(150, (int) $obj->target_value);
        $this->assertSame($psm->id, $obj->user_id);            // owner = partner PSM
        $this->assertSame('manual', $obj->metric_type);
        $this->assertSame('delivery', $obj->metric_config['role']);
        $this->assertSame('Q3-FY27', $obj->metric_config['period']);

        // Re-import with a new target → updates the same objective, no duplicate.
        $file2 = $this->makeWorkbook(['IFP Delivery Target'], [['Deloitte LLP', 175]]);
        $plan2 = $importer->plan($file2, 'delivery', 'Q3-FY27');
        $this->assertSame(1, $plan2['summary']['update']);
        $this->assertSame(0, $plan2['summary']['create']);
        $importer->apply($plan2);

        $this->assertSame(1, PsmObjective::withoutGlobalScopes()->where('partner_id', $partner->id)->count());
        $this->assertEquals(175, (int) $obj->fresh()->target_value);
        @unlink($file);
        @unlink($file2);
    }

    public function test_review_candidates_surface_preexisting_placeholders(): void
    {
        $psm = User::factory()->psm()->create();
        $partner = Partner::factory()->create(['name' => 'Deloitte LLP', 'user_id' => $psm->id]);
        PsmObjective::withoutGlobalScopes()->create([
            'partner_id' => $partner->id,
            'user_id' => $psm->id,
            'title' => 'Get 8 Deloitte resources certified on IFP v2',
            'category' => 'certification',
            'metric_type' => 'manual',
            'target_value' => 8,
            'status' => 'active',
            'target_date' => now()->addMonths(3)->toDateString(),
        ]);

        $file = $this->makeWorkbook(['IFP Delivery Target'], [['Deloitte LLP', 150]]);
        $plan = app(PsmTargetImporter::class)->plan($file, 'delivery', 'Q3-FY27');

        $titles = collect($plan['review_candidates'])->pluck('title');
        $this->assertTrue($titles->contains('Get 8 Deloitte resources certified on IFP v2'));
        @unlink($file);
    }

    public function test_create_missing_partners_creates_and_imports_owned_by_fallback(): void
    {
        $fallback = User::factory()->psm()->create();

        $file = $this->makeWorkbook(['IFP Delivery Target'], [['ARGON Consulting', 12]]);
        $importer = app(PsmTargetImporter::class);

        $plan = $importer->plan($file, 'delivery', 'Q3-FY27', $fallback->id, createMissing: true);
        $this->assertSame(['ARGON Consulting'], $plan['partners_to_create']);
        $this->assertEmpty($plan['unmatched_partners']);

        $counts = $importer->apply($plan);
        $this->assertSame(1, $counts['partners_created']);
        $this->assertSame(1, $counts['created']);

        $partner = Partner::withoutGlobalScopes()->where('name', 'ARGON Consulting')->first();
        $this->assertNotNull($partner);
        $this->assertSame($fallback->id, $partner->user_id);

        $obj = PsmObjective::withoutGlobalScopes()->where('partner_id', $partner->id)->first();
        $this->assertNotNull($obj);
        $this->assertEquals(12, (int) $obj->target_value);
        $this->assertSame($fallback->id, $obj->user_id);

        // Re-run: the partner now exists → matched, not re-created; objective updated.
        $plan2 = $importer->plan($file, 'delivery', 'Q3-FY27', $fallback->id, createMissing: true);
        $this->assertSame([], $plan2['partners_to_create']);
        $counts2 = $importer->apply($plan2);
        $this->assertSame(0, $counts2['partners_created']);
        $this->assertSame(1, Partner::withoutGlobalScopes()->where('name', 'ARGON Consulting')->count());
        @unlink($file);
    }

    public function test_ownerless_partner_objectives_fall_back_to_fallback_owner(): void
    {
        $fallback = User::factory()->psm()->create();
        $partner = Partner::factory()->create(['name' => 'Orphan Co', 'user_id' => null]);

        $file = $this->makeWorkbook(['IFP Delivery Target'], [['Orphan Co', 20]]);
        $importer = app(PsmTargetImporter::class);

        $plan = $importer->plan($file, 'delivery', 'Q3-FY27', $fallback->id);
        $this->assertContains('Orphan Co', $plan['owner_assigned_fallback']);
        $this->assertEmpty($plan['partners_without_owner']);

        $importer->apply($plan);

        $obj = PsmObjective::withoutGlobalScopes()->where('partner_id', $partner->id)->first();
        $this->assertNotNull($obj);
        $this->assertSame($fallback->id, $obj->user_id);
        // The partner itself is not silently reassigned.
        $this->assertNull($partner->fresh()->user_id);
        @unlink($file);
    }

    public function test_ownerless_partner_is_skipped_without_fallback(): void
    {
        Partner::factory()->create(['name' => 'Orphan Co', 'user_id' => null]);
        $file = $this->makeWorkbook(['IFP Delivery Target'], [['Orphan Co', 20]]);

        $plan = app(PsmTargetImporter::class)->plan($file, 'delivery', 'Q3-FY27');

        $this->assertContains('Orphan Co', $plan['partners_without_owner']);
        $this->assertSame(0, $plan['summary']['create']);
        @unlink($file);
    }

    public function test_retire_placeholders_cancels_review_candidates_idempotently(): void
    {
        $psm = User::factory()->psm()->create();
        $partner = Partner::factory()->create(['name' => 'Deloitte LLP', 'user_id' => $psm->id]);
        $placeholder = PsmObjective::withoutGlobalScopes()->create([
            'partner_id' => $partner->id,
            'user_id' => $psm->id,
            'title' => 'Get 8 Deloitte resources certified on IFP v2',
            'category' => 'certification',
            'metric_type' => 'manual',
            'target_value' => 8,
            'status' => 'active',
            'target_date' => now()->addMonths(3)->toDateString(),
        ]);

        $file = $this->makeWorkbook(['IFP Delivery Target'], [['Deloitte LLP', 150]]);
        $importer = app(PsmTargetImporter::class);
        $plan = $importer->plan($file, 'delivery', 'Q3-FY27');

        $counts = $importer->apply($plan, null, retirePlaceholders: true);
        $this->assertSame(1, $counts['retired']);
        $this->assertSame('cancelled', $placeholder->fresh()->status);
        $this->assertSame('psm_target_import', $placeholder->fresh()->metric_config['retired_by']);

        // Idempotent: a second retire pass does nothing to the already-cancelled one.
        $counts2 = $importer->apply($importer->plan($file, 'delivery', 'Q3-FY27'), null, retirePlaceholders: true);
        $this->assertSame(0, $counts2['retired']);
        @unlink($file);
    }
}
