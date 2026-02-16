<?php

namespace Tests\Unit;

use App\Models\AnaplanModel;
use App\Models\AnaplanVersionConfig;
use App\Models\AnaplanWorkspace;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnaplanWorkspaceModelTest extends TestCase
{
    use RefreshDatabase;

    // ─── Workspace Classification ───────────────────────────────────

    public function test_classify_finance_application_v2(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('IFP Applications V2 - Accenture');

        $this->assertEquals('Finance', $result['product_area']);
        $this->assertEquals('Application V2', $result['workspace_type']);
    }

    public function test_classify_finance_application(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('IFP Applications - Deloitte');

        $this->assertEquals('Finance', $result['product_area']);
        $this->assertEquals('Applications', $result['workspace_type']);
    }

    public function test_classify_finance_configurator(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('Finance Configurator - KPMG');

        $this->assertEquals('Finance', $result['product_area']);
        $this->assertEquals('Configurator', $result['workspace_type']);
    }

    public function test_classify_finance_conceptual_demo(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('IFP App Conceptual Demo');

        $this->assertEquals('Finance', $result['product_area']);
        $this->assertEquals('Conceptual Demo', $result['workspace_type']);
    }

    public function test_classify_supply_chain_v2(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('SCM Applications V2 - Partner Co');

        $this->assertEquals('Supply Chain', $result['product_area']);
        $this->assertEquals('Application V2', $result['workspace_type']);
    }

    public function test_classify_supply_chain_applications(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('Supply Chain Applications - KPMG');

        $this->assertEquals('Supply Chain', $result['product_area']);
        $this->assertEquals('Applications', $result['workspace_type']);
    }

    public function test_classify_supply_chain_configurator(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('SCM Configurator - Deloitte');

        $this->assertEquals('Supply Chain', $result['product_area']);
        $this->assertEquals('Configurator', $result['workspace_type']);
    }

    public function test_classify_hr_workforce_v2(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('OWP Applications V2 - PwC');

        $this->assertEquals('HR & Workforce', $result['product_area']);
        $this->assertEquals('Application V2', $result['workspace_type']);
    }

    public function test_classify_hr_workforce_applications(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('HR & Workforce Applications - Partner');

        $this->assertEquals('HR & Workforce', $result['product_area']);
        $this->assertEquals('Applications', $result['workspace_type']);
    }

    public function test_classify_hr_configurator(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('OWP Configurator - Ernst');

        $this->assertEquals('HR & Workforce', $result['product_area']);
        $this->assertEquals('Configurator', $result['workspace_type']);
    }

    public function test_classify_sales_marketing_v2(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('SPM Applications V2 - Cognizant');

        $this->assertEquals('Sales & Marketing', $result['product_area']);
        $this->assertEquals('Application V2', $result['workspace_type']);
    }

    public function test_classify_sales_marketing_applications(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('Sales & Marketing Applications - Partner');

        $this->assertEquals('Sales & Marketing', $result['product_area']);
        $this->assertEquals('Applications', $result['workspace_type']);
    }

    public function test_classify_sales_configurator(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('RPM Configurator - Infosys');

        $this->assertEquals('Sales & Marketing', $result['product_area']);
        $this->assertEquals('Configurator', $result['workspace_type']);
    }

    public function test_classify_unknown_returns_nulls(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('Some Random Workspace Name');

        $this->assertNull($result['product_area']);
        $this->assertNull($result['workspace_type']);
    }

    public function test_classify_with_anaplan_prefix(): void
    {
        $result = AnaplanWorkspace::classifyWorkspace('Anaplan Finance Applications V2 - Partner');

        $this->assertEquals('Finance', $result['product_area']);
        $this->assertEquals('Application V2', $result['workspace_type']);
    }

    // ─── Version Detection ──────────────────────────────────────────

    public function test_detect_version_v2(): void
    {
        $this->assertEquals('V2.0', AnaplanModel::detectVersion('Revenue Hub V2.0 - Finance'));
    }

    public function test_detect_version_v1_3(): void
    {
        $this->assertEquals('V1.3', AnaplanModel::detectVersion('Cash Flow 1.3 Model'));
    }

    public function test_detect_version_v1_2_1(): void
    {
        $this->assertEquals('V1.2.1', AnaplanModel::detectVersion('Expense Model v1.2.1'));
    }

    public function test_detect_version_v1_2(): void
    {
        $this->assertEquals('V1.2', AnaplanModel::detectVersion('Headcount Plan 1.2'));
    }

    public function test_detect_version_v1_1(): void
    {
        $this->assertEquals('V1.1', AnaplanModel::detectVersion('Territory Model V1.1'));
    }

    public function test_detect_version_v0(): void
    {
        $this->assertEquals('V0', AnaplanModel::detectVersion('Spoke V0 Test'));
    }

    public function test_detect_version_demo(): void
    {
        $this->assertEquals('Demo', AnaplanModel::detectVersion('FPT Training Model'));
    }

    public function test_detect_version_demo_conceptual(): void
    {
        $this->assertEquals('Demo', AnaplanModel::detectVersion('Conceptual Demo Classic'));
    }

    public function test_detect_version_base(): void
    {
        $this->assertEquals('Base', AnaplanModel::detectVersion('Base - Financial Model'));
    }

    public function test_detect_version_other_for_unknown(): void
    {
        $this->assertEquals('Other', AnaplanModel::detectVersion('Custom Model No Version'));
    }

    public function test_detect_version_first_returns_other(): void
    {
        $this->assertEquals('Other', AnaplanModel::detectVersion('first'));
        $this->assertEquals('Other', AnaplanModel::detectVersion('First Model'));
    }

    // ─── Model isOutdated ───────────────────────────────────────────

    public function test_model_is_outdated_when_behind_config(): void
    {
        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $workspace = AnaplanWorkspace::factory()->create(['product_area' => 'Finance']);
        $model = AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V1.2',
        ]);

        $this->assertTrue($model->isOutdated());
    }

    public function test_model_is_not_outdated_when_current(): void
    {
        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $workspace = AnaplanWorkspace::factory()->create(['product_area' => 'Finance']);
        $model = AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V2.0',
        ]);

        $this->assertFalse($model->isOutdated());
    }

    public function test_model_is_not_outdated_without_config(): void
    {
        $workspace = AnaplanWorkspace::factory()->create(['product_area' => 'Finance']);
        $model = AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V1.2',
        ]);

        $this->assertFalse($model->isOutdated());
    }

    public function test_demo_version_never_outdated(): void
    {
        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $workspace = AnaplanWorkspace::factory()->create(['product_area' => 'Finance']);
        $model = AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'Demo',
        ]);

        $this->assertFalse($model->isOutdated());
    }

    // ─── Version Badge Color ────────────────────────────────────────

    public function test_version_badge_green_when_current(): void
    {
        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $workspace = AnaplanWorkspace::factory()->create(['product_area' => 'Finance']);
        $model = AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V2.0',
        ]);

        $this->assertEquals('#28a745', $model->versionBadgeColor());
    }

    public function test_version_badge_yellow_when_one_behind(): void
    {
        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $workspace = AnaplanWorkspace::factory()->create(['product_area' => 'Finance']);
        $model = AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V1.3',
        ]);

        $this->assertEquals('#ffc107', $model->versionBadgeColor());
    }

    public function test_version_badge_red_when_two_or_more_behind(): void
    {
        AnaplanVersionConfig::create([
            'product_area' => 'Finance',
            'current_version' => 'V2.0',
        ]);

        $workspace = AnaplanWorkspace::factory()->create(['product_area' => 'Finance']);
        $model = AnaplanModel::factory()->create([
            'anaplan_workspace_id' => $workspace->id,
            'product_area' => 'Finance',
            'detected_version' => 'V1.2',
        ]);

        $this->assertEquals('#dc3545', $model->versionBadgeColor());
    }

    // ─── Workspace Helper Methods ───────────────────────────────────

    public function test_is_assigned_returns_true_with_partner(): void
    {
        $workspace = AnaplanWorkspace::factory()->create();

        $this->assertTrue($workspace->isAssigned());
    }

    public function test_is_assigned_returns_false_without_partner(): void
    {
        $workspace = AnaplanWorkspace::factory()->unassigned()->create();

        $this->assertFalse($workspace->isAssigned());
    }

    public function test_usage_percentage_calculation(): void
    {
        $workspace = AnaplanWorkspace::factory()->create([
            'size_allowance' => 10_000_000,
            'current_size' => 5_000_000,
        ]);

        $this->assertEquals(50.0, $workspace->usagePercentage());
    }

    public function test_usage_percentage_returns_null_without_allowance(): void
    {
        $workspace = AnaplanWorkspace::factory()->create([
            'size_allowance' => null,
            'current_size' => 5_000_000,
        ]);

        $this->assertNull($workspace->usagePercentage());
    }

    public function test_formatted_size_in_gb(): void
    {
        $workspace = AnaplanWorkspace::factory()->create([
            'size_allowance' => 5_368_709_120, // 5 GB
        ]);

        $this->assertEquals('5 GB', $workspace->formattedSizeAllowance());
    }

    public function test_formatted_size_in_mb(): void
    {
        $workspace = AnaplanWorkspace::factory()->create([
            'size_allowance' => 524_288_000, // ~500 MB
        ]);

        $this->assertEquals('500 MB', $workspace->formattedSizeAllowance());
    }

    public function test_formatted_size_returns_na_for_null(): void
    {
        $workspace = AnaplanWorkspace::factory()->create([
            'size_allowance' => null,
        ]);

        $this->assertEquals('N/A', $workspace->formattedSizeAllowance());
    }

    // ─── Model isActive ─────────────────────────────────────────────

    public function test_model_is_active_for_production(): void
    {
        $model = AnaplanModel::factory()->create(['active_state' => 'PRODUCTION']);

        $this->assertTrue($model->isActive());
    }

    public function test_model_is_not_active_when_archived(): void
    {
        $model = AnaplanModel::factory()->archived()->create();

        $this->assertFalse($model->isActive());
    }

    // ─── Link to Partners ───────────────────────────────────────────

    public function test_link_to_partners_matches_by_name(): void
    {
        $psm = User::factory()->create(['role' => 'psm']);
        $partner = Partner::factory()->create(['name' => 'Accenture', 'user_id' => $psm->id]);

        AnaplanWorkspace::factory()->unassigned()->create([
            'name' => 'IFP Applications V2 - Accenture',
        ]);

        $linked = AnaplanWorkspace::linkToPartners();

        $this->assertEquals(1, $linked);
        $this->assertDatabaseHas('anaplan_workspaces', [
            'name' => 'IFP Applications V2 - Accenture',
            'partner_id' => $partner->id,
        ]);
    }

    public function test_link_to_partners_skips_already_assigned(): void
    {
        $psm = User::factory()->create(['role' => 'psm']);
        $partner = Partner::factory()->create(['name' => 'Deloitte', 'user_id' => $psm->id]);

        AnaplanWorkspace::factory()->create([
            'name' => 'IFP Applications - Deloitte',
            'partner_id' => $partner->id,
        ]);

        $linked = AnaplanWorkspace::linkToPartners();

        $this->assertEquals(0, $linked);
    }

    public function test_link_to_partners_returns_zero_for_no_matches(): void
    {
        AnaplanWorkspace::factory()->unassigned()->create([
            'name' => 'IFP Applications - Unknown Corp',
        ]);

        $linked = AnaplanWorkspace::linkToPartners();

        $this->assertEquals(0, $linked);
    }

    // ─── Memory Formatting on AnaplanModel ──────────────────────────

    public function test_model_formatted_memory_in_gb(): void
    {
        $model = AnaplanModel::factory()->create([
            'memory_usage' => 2_147_483_648, // 2 GB
        ]);

        $this->assertEquals('2 GB', $model->formattedMemoryUsage());
    }

    public function test_model_formatted_memory_in_mb(): void
    {
        $model = AnaplanModel::factory()->create([
            'memory_usage' => 524_288_000, // ~500 MB
        ]);

        $this->assertEquals('500 MB', $model->formattedMemoryUsage());
    }

    public function test_model_formatted_memory_na_for_null(): void
    {
        $model = AnaplanModel::factory()->create([
            'memory_usage' => null,
        ]);

        $this->assertEquals('N/A', $model->formattedMemoryUsage());
    }
}
