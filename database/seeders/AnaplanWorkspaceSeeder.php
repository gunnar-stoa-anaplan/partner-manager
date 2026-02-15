<?php

namespace Database\Seeders;

use App\Models\AnaplanList;
use App\Models\AnaplanModel;
use App\Models\AnaplanModule;
use App\Models\AnaplanWorkspace;
use App\Models\Partner;
use Illuminate\Database\Seeder;

class AnaplanWorkspaceSeeder extends Seeder
{
    /** @var array<string, array<string>> Model name templates by version */
    protected static array $versionedModelTemplates = [
        'V2.0' => [
            'Hub V2.0 - {partner}', 'Spoke V2.0 - {partner} Revenue',
            'Spoke V2.0 - {partner} Workforce', 'Spoke V2.0 - {partner} OpEx',
        ],
        'V1.3' => [
            'Hub V1.3 - {partner}', 'Spoke V1.3 - {partner} Revenue',
            'Spoke V1.3 - {partner} OpEx', 'Spoke V1.3 - {partner} Workforce',
        ],
        'V1.2.1' => [
            'Hub V1.2.1 - {partner}', 'Spoke V1.2.1 - {partner} Revenue',
            'Spoke V1.2.1 - {partner} P&L', 'Spoke 1.2.1 - {partner} Workforce',
        ],
        'V1.2' => [
            'Hub V1.2 - {partner}', 'Spoke V1.2 - {partner} Revenue',
            'Spoke V1.2 - {partner} Headcount',
        ],
        'V1.1' => [
            'Hub V1.1 - {partner}', 'Spoke V1.1 - {partner} Revenue',
        ],
    ];

    /** @var array<string, array<string>> Non-versioned model templates per product area */
    protected static array $areaModelTemplates = [
        'Finance' => [
            '{partner} Financial Consolidation', '{partner} Revenue Planning',
            '{partner} Budget Model', '{partner} Cash Flow Forecasting',
        ],
        'Supply Chain' => [
            '{partner} Demand Planning', '{partner} Supply Planning',
            '{partner} S&OP Model', '{partner} Inventory Optimization',
        ],
        'HR & Workforce' => [
            '{partner} Headcount Planning', '{partner} Compensation Model',
            '{partner} Workforce Forecasting', '{partner} Talent Planning',
        ],
        'Sales & Marketing' => [
            '{partner} Territory Planning', '{partner} Quota Management',
            '{partner} Sales Forecasting', '{partner} Marketing Mix Model',
        ],
    ];

    protected static array $moduleNames = [
        'Revenue Inputs', 'P&L Summary', 'Assumptions', 'Data Hub',
        'Reporting Module', 'FX Rates', 'Employee Master', 'Cost Allocation',
        'Dashboard Data', 'Driver Inputs', 'Actuals Import', 'Variance Analysis',
        'KPI Scorecard', 'Scenario Comparison', 'Time Settings',
    ];

    protected static array $listNames = [
        'Accounts', 'Cost Centers', 'Products', 'Time Periods',
        'Departments', 'Entities', 'Versions', 'Scenarios',
        'Employees', 'Projects', 'Regions', 'Customers',
    ];

    /** @var array<string, string> Map product area to actual Anaplan workspace prefix */
    protected static array $areaPrefixes = [
        'Finance' => 'IFP',
        'Supply Chain' => 'SCM',
        'HR & Workforce' => 'OWP',
        'Sales & Marketing' => 'SPM',
    ];

    public function run(): void
    {
        $partners = Partner::orderBy('id')->take(10)->get();
        $syncedAt = now()->subMinutes(rand(5, 120));

        // Per-partner config: which product areas they have and at what version
        $partnerConfigs = [
            ['Finance' => 'V2.0', 'Supply Chain' => 'V1.3', 'HR & Workforce' => 'V2.0', 'Sales & Marketing' => 'V1.2.1'],
            ['Finance' => 'V2.0', 'Supply Chain' => 'V1.2.1', 'HR & Workforce' => 'V1.3'],
            ['Finance' => 'V1.3', 'Sales & Marketing' => 'V1.2'],
            ['Finance' => 'V1.2.1', 'Supply Chain' => 'V1.2.1', 'HR & Workforce' => 'V1.2', 'Sales & Marketing' => 'V1.3'],
            ['Finance' => 'V1.2.1'],
            ['Finance' => 'V1.2', 'Supply Chain' => 'V1.3'],
            ['Finance' => 'V1.2.1', 'HR & Workforce' => 'V1.2.1', 'Sales & Marketing' => 'V2.0'],
            ['Finance' => 'V2.0', 'HR & Workforce' => 'V1.1'],
            ['Finance' => 'V1.1'],
            ['Finance' => 'V2.0', 'Supply Chain' => 'V2.0', 'HR & Workforce' => 'V2.0', 'Sales & Marketing' => 'V2.0'],
        ];

        foreach ($partners as $i => $partner) {
            $config = $partnerConfigs[$i] ?? ['Finance' => 'V1.2.1'];

            foreach ($config as $area => $version) {
                $prefix = self::$areaPrefixes[$area] ?? $area;
                $wsName = $prefix.' Applications - '.$partner->name;
                $this->seedWorkspace($partner, $area, $wsName, 'Applications', $version, $syncedAt);
            }
        }

        // 2 unassigned workspaces
        foreach (['Sandbox Training Workspace', 'New Client Workspace'] as $name) {
            $workspace = AnaplanWorkspace::factory()->unassigned()->create([
                'name' => $name,
                'last_synced_at' => $syncedAt,
            ]);
            $this->seedModelsForWorkspace($workspace, $syncedAt, null, [
                'Revenue Planning', 'Headcount Plan', 'Budget Model',
            ]);
        }
    }

    private function seedWorkspace(
        Partner $partner,
        string $productArea,
        string $workspaceName,
        string $workspaceType,
        string $version,
        \DateTimeInterface $syncedAt,
    ): void {
        $classification = AnaplanWorkspace::classifyWorkspace($workspaceName);

        $workspace = AnaplanWorkspace::factory()->create([
            'name' => $workspaceName,
            'partner_id' => $partner->id,
            'workspace_type' => $classification['workspace_type'] ?: $workspaceType,
            'product_area' => $classification['product_area'] ?: $productArea,
            'last_synced_at' => $syncedAt,
        ]);

        // Use versioned templates if we have them, otherwise area-specific templates
        $templates = self::$versionedModelTemplates[$version] ?? [];
        if (! empty($templates)) {
            $modelCount = rand(2, min(4, count($templates)));
            $selectedTemplates = collect($templates)->shuffle()->take($modelCount);
        } else {
            $areaTemplates = self::$areaModelTemplates[$productArea] ?? self::$areaModelTemplates['Finance'];
            $modelCount = rand(2, min(3, count($areaTemplates)));
            $selectedTemplates = collect($areaTemplates)->shuffle()->take($modelCount);
        }

        $modelNames = $selectedTemplates->map(fn ($t) => str_replace('{partner}', $partner->name, $t))->toArray();

        $this->seedModelsForWorkspace($workspace, $syncedAt, $productArea, $modelNames);
    }

    private function seedModelsForWorkspace(
        AnaplanWorkspace $workspace,
        \DateTimeInterface $syncedAt,
        ?string $productArea,
        array $modelNames,
    ): void {
        foreach ($modelNames as $modelName) {
            $moduleCount = rand(3, 8);
            $listCount = rand(2, 6);
            $detectedVersion = AnaplanModel::detectVersion($modelName);

            $model = AnaplanModel::factory()->create([
                'name' => $modelName,
                'anaplan_workspace_id' => $workspace->id,
                'detected_version' => $detectedVersion,
                'product_area' => $productArea,
                'module_count' => $moduleCount,
                'list_count' => $listCount,
                'last_synced_at' => $syncedAt,
            ]);

            $moduleSelection = collect(self::$moduleNames)->shuffle()->take($moduleCount);
            foreach ($moduleSelection as $moduleName) {
                AnaplanModule::factory()->create([
                    'name' => $moduleName,
                    'anaplan_model_id' => $model->id,
                ]);
            }

            $listSelection = collect(self::$listNames)->shuffle()->take($listCount);
            foreach ($listSelection as $listName) {
                AnaplanList::factory()->create([
                    'name' => $listName,
                    'anaplan_model_id' => $model->id,
                ]);
            }
        }
    }
}
