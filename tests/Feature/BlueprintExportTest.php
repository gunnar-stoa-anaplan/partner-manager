<?php

namespace Tests\Feature;

use App\Models\Blueprint;
use App\Models\BlueprintContentItem;
use App\Models\BlueprintOutcome;
use App\Models\User;
use App\Services\BlueprintExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithActivationSetup;
use ZipArchive;

class BlueprintExportTest extends TestCase
{
    use RefreshDatabase, WithActivationSetup;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->activateModuleForUser('enablement_admin', $this->admin);
    }

    public function test_admin_can_export_blueprint(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('blueprints.export', $blueprint));

        $response->assertDownload();
    }

    public function test_export_filename_matches_blueprint_slug(): void
    {
        $blueprint = Blueprint::factory()->published()->create([
            'name' => 'ADO Enablement',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('blueprints.export', $blueprint));

        $response->assertDownload('ado-enablement-blueprint.pptx');
    }

    public function test_export_produces_valid_pptx(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create(['blueprint_id' => $blueprint->id, 'sort_order' => 1]);
        BlueprintContentItem::factory()->requiredForAll()->create([
            'blueprint_outcome_id' => $outcome->id,
            'title' => 'Test Content',
            'duration_display' => '30 min',
            'source' => 'Academy',
        ]);

        $service = new BlueprintExportService;
        $blueprint->load('outcomes.contentItems');
        $path = $service->export($blueprint);

        // PPTX is a valid zip
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $this->assertNotFalse($zip->getFromName('ppt/slides/slide2.xml'));
        $this->assertNotFalse($zip->getFromName('ppt/slides/slide3.xml'));
        $zip->close();

        unlink($path);
    }

    public function test_export_slide2_contains_blueprint_name(): void
    {
        $blueprint = Blueprint::factory()->published()->create([
            'name' => 'Finance Enablement',
            'created_by' => $this->admin->id,
        ]);

        $service = new BlueprintExportService;
        $path = $service->export($blueprint);

        $zip = new ZipArchive;
        $zip->open($path);
        $slide2 = $zip->getFromName('ppt/slides/slide2.xml');
        $zip->close();

        $this->assertStringContainsString('Finance Enablement', $slide2);

        unlink($path);
    }

    public function test_export_slide3_contains_outcome_and_content(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);
        $outcome = BlueprintOutcome::factory()->create([
            'blueprint_id' => $blueprint->id,
            'sort_order' => 1,
            'title' => 'Partners can deploy widgets',
        ]);
        BlueprintContentItem::factory()->create([
            'blueprint_outcome_id' => $outcome->id,
            'title' => 'Widget Deployment Guide',
            'source' => 'Seismic',
        ]);

        $service = new BlueprintExportService;
        $blueprint->load('outcomes.contentItems');
        $path = $service->export($blueprint);

        $zip = new ZipArchive;
        $zip->open($path);
        $slide3 = $zip->getFromName('ppt/slides/slide3.xml');
        $zip->close();

        $this->assertStringContainsString('Partners can deploy widgets', $slide3);
        $this->assertStringContainsString('Widget Deployment Guide', $slide3);
        $this->assertStringContainsString('Seismic', $slide3);

        unlink($path);
    }

    public function test_export_for_blueprint_without_outcomes(): void
    {
        $blueprint = Blueprint::factory()->published()->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('blueprints.export', $blueprint));

        $response->assertDownload();
    }

    public function test_export_requires_authentication(): void
    {
        $blueprint = Blueprint::factory()->published()->create();

        $response = $this->get(route('blueprints.export', $blueprint));

        $response->assertRedirect(route('login'));
    }

    public function test_export_handles_special_characters_in_name(): void
    {
        $blueprint = Blueprint::factory()->published()->create([
            'name' => 'Supply & Demand <Planning>',
            'created_by' => $this->admin->id,
        ]);

        $service = new BlueprintExportService;
        $path = $service->export($blueprint);

        $zip = new ZipArchive;
        $zip->open($path);
        $slide2 = $zip->getFromName('ppt/slides/slide2.xml');
        $zip->close();

        // XML entities should be escaped properly
        $this->assertStringContainsString('Supply &amp; Demand &lt;Planning&gt;', $slide2);

        unlink($path);
    }
}
