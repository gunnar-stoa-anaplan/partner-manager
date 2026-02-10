<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Blueprint;
use App\Models\BlueprintOutcome;
use App\Models\BlueprintContentItem;
use App\Models\User;

class BlueprintSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Enablement Blueprints from PDF data...');

        $adminId = User::where('role', 'admin')->first()->id ?? 1;

        $this->seedPolaris($adminId);
        $this->seedInventoryDemandPlanning($adminId);
        $this->seedIntegratedBusinessPlanning($adminId);
        $this->seedMerchandiseFinancialPlanning($adminId);
        $this->seedIFP($adminId);
        $this->seedFPC($adminId);
        $this->seedFCR($adminId);
        $this->seedAXL($adminId);
        $this->seedADM($adminId);
        $this->seedRPM($adminId);
        $this->seedOWP($adminId);
        $this->seedForecaster($adminId);

        $count = Blueprint::count();
        $this->command->info("Seeded {$count} blueprints successfully.");
    }

    private function createBlueprint(int $adminId, string $name, string $category, ?string $productArea, string $version, ?string $description = null): Blueprint
    {
        return Blueprint::create([
            'name' => $name,
            'category' => $category,
            'product_area' => $productArea,
            'version' => $version,
            'description' => $description,
            'status' => 'published',
            'created_by' => $adminId,
        ]);
    }

    private function addItems(BlueprintOutcome $outcome, array $items): void
    {
        foreach ($items as $i => $item) {
            BlueprintContentItem::create([
                'blueprint_outcome_id' => $outcome->id,
                'sort_order' => $i + 1,
                'title' => $item['title'],
                'url' => $item['url'] ?? null,
                'duration_minutes' => $item['duration_minutes'] ?? null,
                'duration_display' => $item['duration_display'] ?? null,
                'source' => $item['source'] ?? null,
                'prerequisites' => $item['prerequisites'] ?? null,
                'gtm_applicable' => $item['gtm'] ?? 'na',
                'pre_sales_applicable' => $item['pre_sales'] ?? 'na',
                'delivery_applicable' => $item['delivery'] ?? 'na',
            ]);
        }
    }

    // ─── Polaris ───────────────────────────────────────────────

    private function seedPolaris(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Polaris', 'application', 'Platform', 'V1.3',
            'App Enablement Prerequisite: Polaris Enablement. Required foundation for all application blueprints.');

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'PLANUAL now updated to include Polaris', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Differences between calculation engines', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Calculation Engines — Statement of Direction: 2025', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Support', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'OEG Best Practice Series : Polaris', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: Polaris (1 outcome, 5 items)");
    }

    // ─── Inventory & Demand Planning ───────────────────────────

    private function seedInventoryDemandPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Inventory & Demand Planning', 'application', 'Supply Chain', 'V1.3',
            'Supply Chain application blueprint covering demand planning, forecasting, and inventory optimization.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Inventory & Demand Planning (familiarize yourself with available materials)', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '01. An Introduction to Anaplan\'s Supply Chain Planning Applications', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '05. Customer Stories', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Business Value Framework (BVS) for Supply Chain', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'SCM Transformation – First Meeting Deck', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => '02. How to sell SCP Applications', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => '03. Demand Planning and Forecasting', 'duration_display' => '1.5 hours', 'duration_minutes' => 90, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '04. Inventory Planning', 'duration_display' => '50 min', 'duration_minutes' => 50, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '07. Data Hub', 'duration_display' => '40 min', 'duration_minutes' => 40, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '08. Implementation', 'duration_display' => '35 min', 'duration_minutes' => 35, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '09. Rough Effort Estimation', 'duration_display' => '17 min', 'duration_minutes' => 17, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '10. Customization Support and Upgrade', 'duration_display' => '27 min', 'duration_minutes' => 27, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '11. Partner Support for Implementation', 'duration_display' => '9 min', 'duration_minutes' => 9, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Regional in-person SC Apps GTM workshop', 'duration_display' => '2 days', 'duration_minutes' => 960, 'source' => 'App COE', 'prerequisites' => 'GTM / Presales 1+2+3', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Regional in-person SC Apps Implementer workshop', 'duration_display' => '3 days', 'duration_minutes' => 1440, 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3+4', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Anaplan', 'prerequisites' => 'SC Apps GTM or Implementer Workshop', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'source' => 'Anaplan', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: Inventory & Demand Planning (6 outcomes)");
    }

    // ─── Integrated Business Planning ──────────────────────────

    private function seedIntegratedBusinessPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Integrated Business Planning', 'application', 'Supply Chain', 'V1.3',
            'Supply Chain application blueprint for integrated business planning across sales, operations, and finance.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Integrated Business Planning (familiarize yourself with available materials)', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '01. An Introduction to Anaplan\'s Supply Chain Planning Applications', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '05. Customer Stories', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IBP – Positioning and Messaging', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IBP Data Sheet', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Integrated Business Planning (IBP) App 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Business Value Framework (BVS) for Supply Chain', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'SCM Transformation – First Meeting Deck', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Ideal Customer Profile - IBP', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'IBP Pitch Deck', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => '02. How to sell SCP Applications', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'Integrated Business Planning (IBP) Configuration', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Integrated Business Planning (IBP) Overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Global IBP Deep-Dive Workshop (invitation-only)', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'App COE', 'prerequisites' => 'SC Apps GTM or Implementer Workshop', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Anaplan', 'prerequisites' => 'Global IBP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'IBP Conceptual Demo copied into Partner WS', 'source' => 'Anaplan', 'prerequisites' => 'Global IBP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'source' => 'Anapedia', 'prerequisites' => 'Global IBP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: Integrated Business Planning (6 outcomes)");
    }

    // ─── Merchandise Financial Planning ────────────────────────

    private function seedMerchandiseFinancialPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Merchandise Financial Planning', 'application', 'Supply Chain', 'V1.3',
            'Supply Chain application blueprint for retail merchandise financial planning.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Merchandise Financial Planning (familiarize yourself with available materials)', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '01. An Introduction to Anaplan\'s Supply Chain Planning Applications', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '05. Customer Stories', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'MFP Positioning and Messaging', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'MFP Data Sheet', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Merchandise Financial Planning (MFP) App 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Business Value Framework (BVS) for Supply Chain', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'SCM Transformation – First Meeting Deck', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Ideal Customer Profile - MFP', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'MFP Pitch Deck', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => '02. How to sell SCP Applications', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'Merchandise Financial Planning Application Configuration', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Merchandise Financial Planning Application Overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Global MFP Deep-Dive Workshop (invitation-only)', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'App COE', 'prerequisites' => 'SC Apps GTM or Implementer Workshop', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Anaplan', 'prerequisites' => 'Global MFP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'MFP Conceptual Demo copied into Partner WS', 'source' => 'Anaplan', 'prerequisites' => 'Global MFP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'source' => 'Anapedia', 'prerequisites' => 'Global MFP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: Merchandise Financial Planning (6 outcomes)");
    }

    // ─── IFP ───────────────────────────────────────────────────

    private function seedIFP(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Integrated Financial Planning (IFP)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint for integrated financial planning and analysis.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IFP App 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Finance Planning Transformation: Sales Play for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Finance Planning Transformation - Discrete Manufacturing: Sales Play for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'optional', 'pre_sales' => 'optional', 'delivery' => 'na'],
            ['title' => 'Financial Planning Transformation for Financial Services for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'optional', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'IFP - Data Hub Setup', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'IFP - Configurator', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Deep Dive Enablement by Region / Country', 'duration_display' => '90 mins', 'duration_minutes' => 90, 'source' => 'App COE', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'required'],
            ['title' => 'Regional in-person IFP Apps Implementer workshop', 'duration_display' => '2 days', 'duration_minutes' => 960, 'source' => 'App COE', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS / CoE (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Tenant', 'prerequisites' => 'Deep Dive Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'source' => 'Tenant', 'prerequisites' => 'Deep Dive Enablement', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Shared Folder with Additional App Documentation', 'source' => 'Sharepoint', 'prerequisites' => 'Deep Dive Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'source' => 'Anapedia', 'prerequisites' => 'Deep Dive Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: IFP (6 outcomes)");
    }

    // ─── FPC ───────────────────────────────────────────────────

    private function seedFPC(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'FPC (Financial Planning & Consolidation)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint covering FPC, Consolidation, Disclosure Management & AnaplanXL.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand FPC']);
        $this->addItems($o1, [
            ['title' => 'FPC Sales & Marketing Highspot', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Blueprint', 'duration_display' => '30 hours', 'duration_minutes' => 1800, 'source' => 'Various', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IFP Blueprint', 'duration_display' => '10 hours', 'duration_minutes' => 600, 'source' => 'Various', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Data Orchestrator (ADO) Training', 'duration_display' => '6 hours', 'duration_minutes' => 360, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: FPC (1 outcome)");
    }

    // ─── FCR ───────────────────────────────────────────────────

    private function seedFCR(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'FCR (Financial Close & Reporting)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint for financial close, consolidation, and reporting.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'FCR Positioning Statement', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Sales Play', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'IFRS Accelerator GTM', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'Introduction to Anaplan Consolidations', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL Fundamentals', 'duration_display' => '3 hours', 'duration_minutes' => 180, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Consolidations Fundamentals', 'duration_display' => '12 hours', 'duration_minutes' => 720, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Consolidations Implementer', 'duration_display' => '12 hours', 'duration_minutes' => 720, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'not applicable', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Deep-Dive Enablement by region/country', 'duration_display' => '90 mins', 'duration_minutes' => 90, 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'required'],
            ['title' => 'Regional in-person Implementer workshop', 'duration_display' => '2.5 days', 'duration_minutes' => 1200, 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision AnaplanXL, FCR', 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'App COE', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Enablement Highspot Page', 'duration_display' => '4 hours', 'duration_minutes' => 240, 'source' => 'Highspot', 'prerequisites' => '2 learners completed 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'IFRS Accelerator & EMEA Demo Script / Demo Videos', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'App COE', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $this->command->info("  Created: FCR (6 outcomes)");
    }

    // ─── AXL ───────────────────────────────────────────────────

    private function seedAXL(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'AXL (AnaplanXL)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint for AnaplanXL reporting and analytics.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'FCR Positioning Statement', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Pitch Deck & ICP', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'AnaplanXL Reporting Training', 'duration_display' => '14 hours', 'duration_minutes' => 840, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL How-to Videos', 'duration_display' => '2 hours', 'duration_minutes' => 120, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [['title' => 'not applicable', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na']]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision AnaplanXL', 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Join & Participate in the Community', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Technical Documentation', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'App COE', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Enablement Seismic Page', 'duration_display' => '4 hours', 'duration_minutes' => 240, 'source' => 'Seismic', 'prerequisites' => '2 learners completed 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: AXL (6 outcomes)");
    }

    // ─── ADM ───────────────────────────────────────────────────

    private function seedADM(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'ADM (Anaplan Disclosure Management)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint for disclosure management and regulatory reporting.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Data Sheet', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Pitch Deck & ICP', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'AnaplanXL Fundamentals', 'duration_display' => '3 hours', 'duration_minutes' => 180, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Disclosure Management Fundamentals', 'duration_display' => '6 hours', 'duration_minutes' => 360, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL How-to Videos', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [['title' => 'not applicable', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na']]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision AnaplanXL, DM', 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'Anapedia', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $this->command->info("  Created: ADM (6 outcomes)");
    }

    // ─── RPM ───────────────────────────────────────────────────

    private function seedRPM(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Revenue Performance Management (RPM)', 'application', 'Sales', 'V1.3',
            'Sales application blueprint covering segmentation, capacity planning, territory & quota, and Salesforce integration.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Segmentation and Scoring Application 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Go-to-Market Capacity Planning Application 101 Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Territory & Quota Application 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan for Salesforce 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Go-to-Market Planning Applications', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Segmentation and Scoring Pitch deck', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Go-To-Market Capacity Planning Pitch deck', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Territory & Quota Pitch deck', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'RPM Configuration', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Segmentation and Scoring Overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Go-to-Market Capacity Planning Overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Territory & Quota Overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan for Salesforce: Connector Overview', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan for Salesforce: Connector Configuration', 'duration_display' => '45 min', 'duration_minutes' => 45, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Live Sales Enablement (coming soon)', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'App COE', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'optional', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Quarterly Hands-on Enablement (FY26Q3)', 'duration_display' => '2 days', 'duration_minutes' => 960, 'source' => 'App COE', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant (on-demand)', 'source' => 'Tenant', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Conceptual Demos copied into Partner Workspace (coming soon)', 'source' => 'Tenant', 'prerequisites' => '1 + 2', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Conceptual Demos companion assets (coming soon)', 'source' => 'Highspot', 'prerequisites' => '1 + 2', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'source' => 'Anapedia', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: RPM (6 outcomes)");
    }

    // ─── OWP v2 ────────────────────────────────────────────────

    private function seedOWP(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Operational Workforce Planning (OWP v2)', 'application', 'Workforce', 'V1.3',
            'Workforce application blueprint for operational workforce planning.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Operational Workforce Planning Overview', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Operational Workforce Planning Application Datasheet', 'duration_display' => '10 mins', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Operational Workforce Planning (OWP) App 101 for Partners (coming mid-Feb)', 'duration_display' => '1 hr 5 min', 'duration_minutes' => 65, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Workforce Planning Transformation (WPT)', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'OWP Application Initialization (coming mid-Feb)', 'duration_display' => '15 mins', 'duration_minutes' => 15, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Operational Workforce Planning Application Overview (coming mid-Feb)', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Deep Dive Enablement by Region / Country', 'duration_display' => '90 mins', 'duration_minutes' => 90, 'source' => 'App COE', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS / CoE (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Tenant', 'prerequisites' => 'Delivery 1 + 3 + 4 + 5', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Access to Conceptual demo Assets', 'source' => 'Tenant', 'prerequisites' => 'PreSales 1 + 2 + 4 + 5', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'source' => 'Anapedia', 'prerequisites' => 'Delivery 1 + 3 + 4 + 5', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info("  Created: OWP v2 (6 outcomes)");
    }

    // ─── Forecaster ────────────────────────────────────────────

    private function seedForecaster(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Forecaster', 'product', 'Platform', 'V1.0',
            'Product enablement blueprint for Anaplan Forecaster — predictive analytics and forecasting.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand how to position the product\'s value proposition']);
        $this->addItems($o1, [
            ['title' => 'Forecaster 101', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Data Sheet', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Pitch Deck', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand configurations and setup']);
        $this->addItems($o2, [
            ['title' => 'Forecaster Technical Guide', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Documentation on Anapedia', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Academy Training', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand implementation and demo best practice']);
        $this->addItems($o3, [
            ['title' => 'Hands-on Enablement with PS / CoE (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o4, [
            ['title' => 'Partner tenant(s) provisioned with product functionality', 'source' => 'Tenant', 'prerequisites' => 'Request with PSM', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'source' => 'Tenant', 'prerequisites' => 'Demo Assessment', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $this->command->info("  Created: Forecaster (4 outcomes)");
    }
}
