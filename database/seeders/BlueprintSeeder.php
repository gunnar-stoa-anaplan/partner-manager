<?php

namespace Database\Seeders;

use App\Models\Blueprint;
use App\Models\BlueprintContentItem;
use App\Models\BlueprintOutcome;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlueprintSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Enablement Blueprints V2.0...');

        $adminId = User::where('role', 'admin')->first()->id ?? 1;

        $this->seedPolarisEnablement($adminId);
        $this->seedPolarisWorkshop($adminId);
        $this->seedADOEnablement($adminId);
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
        $this->seedDeliveryExcellencePolaris($adminId);
        $this->seedDeliveryExcellenceADO($adminId);
        $this->seedDeliveryExcellenceForecaster($adminId);

        $count = Blueprint::count();
        $this->command->info("Seeded {$count} blueprints successfully.");
    }

    private function createBlueprint(int $adminId, string $name, string $category, ?string $productArea, string $version, ?string $description = null, string $status = 'published'): Blueprint
    {
        return Blueprint::create([
            'name' => $name,
            'category' => $category,
            'product_area' => $productArea,
            'version' => $version,
            'description' => $description,
            'status' => $status,
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

    // --- Polaris Enablement ─────────────────────────────────────

    private function seedPolarisEnablement(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Polaris Enablement', 'application', 'Platform', 'V2.0',
            'App Enablement Prerequisite: Polaris Enablement. Required foundation for all Polaris-based application blueprints.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand Polaris']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Polaris', 'url' => 'https://academy.anaplan.com/learn/courses/10/anaplan-polaris/lessons', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'PLANUAL now updated to include Polaris', 'url' => 'https://support.anaplan.com/polaris-c265a8ac-e457-46a7-b307-1fe62ffa4730', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Differences between calculation engines', 'url' => 'https://help.anaplan.com/differences-between-calculation-engines-88334098-e311-4496-b6c2-a5fecf0e3b47', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Calculation Engines — Statement of Direction: 2025', 'url' => 'https://help.anaplan.com/-anaplan-calculation-engines--statement-of-direction-2025-a68b54b9-170b-4584-867a-6b6600914a31', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Support', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'OEG Best Practice Series : Polaris', 'url' => 'https://community.anaplan.com/categories/best-practices?tagID=713', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Polaris Enablement (1 outcomes, 5 items)');
    }

    // --- Polaris Workshop ───────────────────────────────────────

    private function seedPolarisWorkshop(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Polaris Workshop', 'application', 'Platform', 'V2.0',
            'Polaris workshop blueprint covering live workshops, hands-on enablement, and provisioning.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand Polaris']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o2, [
            ['title' => 'Regional in-person SC Apps GTM workshop', 'duration_display' => '3 days', 'duration_minutes' => 1440, 'source' => 'App COE', 'prerequisites' => 'Presales 1+2', 'gtm' => 'optional', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Regional in-person SC Apps Implementer workshop', 'duration_display' => '3 days', 'duration_minutes' => 1440, 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3+4', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Virtual Office Hours w/ CoE', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'App COE', 'gtm' => 'optional', 'pre_sales' => 'optional', 'delivery' => 'optional'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o3, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'App COE', 'prerequisites' => 'SC Apps GTM Workshop', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'source' => 'App COE', 'prerequisites' => 'SC Apps GTM Workshop', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (https://applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'PSM', 'prerequisites' => '2 SAs completed 1+3+4', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Shared Folder w/ additional App Documentation', 'source' => 'App COE', 'prerequisites' => '2 SAs completed 1+3+4', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Polaris Workshop (3 outcomes, 9 items)');
    }

    // --- ADO Enablement ─────────────────────────────────────────

    private function seedADOEnablement(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'ADO Enablement', 'application', 'Platform', 'V2.0',
            'Application enablement blueprint for Anaplan Data Orchestrator (ADO) covering certification, positioning, and data management.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand ADO']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Data Orchestrator (ADO)', 'url' => 'https://academy.anaplan.com/learn/learning-plans/25/anaplan-data-orchestrator-ado-certification', 'duration' => '6 Hours', 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'ADO 5 Challenges Video', 'url' => 'https://anaplan.seismic.com/apps/doccenter/f4c8d4fe-f6c0-46d8-85cc-d94645d413ce/doc/%25252Fdd434fd8b9-9551-65e4-1b84-7cc32af3d43e%25252FdfNjhjNWQyNGUtMzdhOC00ZDYzLWE5NzQtYmJhZTgyYTQyNjg4%25252CPT0%25253D%25252CU29sdXRpb24gUHJvbW8gVmlkZW8%25253D%25252Flfc858f88c-44df-4c68-a6ed-013de5412116/?mode=viewproperties&searchId=f07d9d68-77db-4a8a-9411-e2ba92d05a7e', 'duration' => '5 min', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Dataspaces', 'url' => 'https://community.anaplan.com/kb/articles/153845-dataspaces', 'duration' => '15 min', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: ADO Enablement (1 outcomes, 3 items)');
    }

    // --- Inventory & Demand Planning ────────────────────────────

    private function seedInventoryDemandPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Inventory & Demand Planning', 'application', 'Supply Chain', 'V2.0',
            'Supply Chain application blueprint covering demand planning, forecasting, and inventory optimization.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/377/anaplan-applications-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Inventory & Demand Planning (familiarize yourself with available materials)', 'url' => 'https://anaplan.seismic.com/apps/search?appType=All&keyword=Inventory%2520Planning%2520Demand%2520Planning&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%20Language%3AEnglish%3BAuto-detected%20Language%3AGerman%3BAuto-detected%20Language%3ASpanish%3B&pageIndex=1&strategy=', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '01. An Introduction to Anaplan\'s Supply Chain Planning Applications', 'url' => 'https://anaplan.seismic.com/Link/Content/DCBBX6mQXJ6288qVPBGbgdXDcCPj', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '05. Customer Stories', 'url' => 'https://anaplan.seismic.com/Link/Content/DCbj2BVmfRh9RGMVgcJD6gFCRG9V', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Business Value Framework (BVS) for Supply Chain', 'url' => 'https://anaplan.seismic.com/apps/search?appType=DocCenter&keyword=Business%2520Value%2520Framework%2520(BVS)%2520for%2520Supply%2520Chain&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%20Language%3AEnglish%3BAuto-detected%20Language%3AGerman%3BAuto-detected%20Language%3ASpanish%3B&pageIndex=0&strategy=', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'SCM Transformation – First Meeting Deck', 'url' => 'https://anaplan.seismic.com/apps/search?appType=DocCenter&keyword=SCM%2520Transformation%2520%25E2%2580%2593%2520First%2520Meeting%2520Deck&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%2520Language%3AEnglish%3BAuto-detected%2520Language%3AGerman%3BAuto-detected%2520Language%3ASpanish%3B&pageIndex=0&strategy=', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => '02. How to sell SCP Applications', 'url' => 'https://anaplan.seismic.com/Link/Content/DCR432fT46mpM8TFbfB22h4JPq2P', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application functionality, configurations, and setup']);
        $this->addItems($o3, [
            ['title' => '03. Demand Planning and Forecasting', 'url' => 'https://anaplan.seismic.com/Link/Content/DCcj39FHFBQ6MGcDhbbcgq4Xqq8d', 'duration_display' => '1.5 hours', 'duration_minutes' => 90, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '04. Inventory Planning', 'url' => 'https://anaplan.seismic.com/Link/Content/DCRVFm7DJGBD48WGfGBMpHHPGRqP', 'duration_display' => '50 min', 'duration_minutes' => 50, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '07. Data Hub', 'url' => 'https://anaplan.seismic.com/Link/Content/DCgTCT8GHjpQVG2JVDQ89mVJTWDB', 'duration_display' => '40 min', 'duration_minutes' => 40, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '08. Implementation', 'url' => 'https://anaplan.seismic.com/Link/Content/DCgqHj4b6B7PT87Hq3G9Q8qm3WDP', 'duration_display' => '35 min', 'duration_minutes' => 35, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '09. Rough Effort Estimation', 'url' => 'https://anaplan.seismic.com/Link/Content/DCHmFX7J7b4hgGCMjmbVPVCcTdbV', 'duration_display' => '17 min', 'duration_minutes' => 17, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '10. Customization Support and Upgrade', 'url' => 'https://anaplan.seismic.com/Link/Content/DCM3d3J9JjMmQG9CjQ2FPCPgXhMV', 'duration_display' => '27 min', 'duration_minutes' => 27, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '11. Partner Support for Implementation', 'url' => 'https://anaplan.seismic.com/Link/Content/DCJ87fVWB6cgT8CQ87TBgXpd8JDG', 'duration_display' => '9 min', 'duration_minutes' => 9, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand dependent components']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners provisioned access to application']);
        $this->addItems($o5, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Anaplan', 'prerequisites' => 'SC Apps GTM or Implementer Workshop', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Technical Documentation (https://applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners receive hands-on experience with application configuration, methodology, and extension']);
        $this->addItems($o6, [
            ['title' => 'Regional in-person SC Apps GTM workshop', 'duration_display' => '2 days', 'duration_minutes' => 960, 'source' => 'App COE', 'prerequisites' => 'GTM / Presales 1+2+3', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Regional in-person SC Apps Implementer workshop', 'duration_display' => '3 days', 'duration_minutes' => 1440, 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3+4', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'required'],
        ]);

        $o7 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 7, 'title' => 'Partners can Demo']);
        $this->addItems($o7, [
            ['title' => 'Conceptual Demo Assessment (read here for further instructions)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'source' => 'Anaplan', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o8 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 8, 'title' => 'Partners have completed "delivery readiness"']);
        $this->addItems($o8, [
            ['title' => 'First project is primed by PS, Partner co-prime, 
Second is primed by Partner, co-primed by PS, 
Third is primed by Partner with PS on call', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o9 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 9, 'title' => 'Partners can independently deliver']);
        $this->addItems($o9, [
            ['title' => 'Partners have co-delivered with Anaplan', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Inventory & Demand Planning (9 outcomes, 23 items)');
    }

    // --- Integrated Business Planning ───────────────────────────

    private function seedIntegratedBusinessPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Integrated Business Planning', 'application', 'Supply Chain', 'V2.0',
            'Supply Chain application blueprint for integrated business planning across sales, operations, and finance.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/377/anaplan-applications-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Integrated Business Planning (familiarize yourself with available materials)', 'url' => 'https://anaplan.seismic.com/apps/search?appType=All&keyword=Integrated%2520Business%2520Planning%2520IBP&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%20Language%3AEnglish%3BAuto-detected%20Language%3AGerman%3BAuto-detected%20Language%3ASpanish%3B&pageIndex=0&strategy=', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '01. An Introduction to Anaplan\'s Supply Chain Planning Applications', 'url' => 'https://anaplan.seismic.com/Link/Content/DCBBX6mQXJ6288qVPBGbgdXDcCPj', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '05. Customer Stories', 'url' => 'https://anaplan.seismic.com/Link/Content/DCbj2BVmfRh9RGMVgcJD6gFCRG9V', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IBP – Positioning and Messaging', 'url' => 'https://anaplan.seismic.com/Link/Content/DC6cqqc33jq7TGQJ3bVmgT6c334G', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IBP Data Sheet', 'url' => 'https://anaplan.seismic.com/Link/Content/DCBqmh7H88jVT84PWDXQb29hbpbB', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Integrated Business Planning (IBP) App 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/413/integrated-business-planning-ibp-app-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Business Value Framework (BVS) for Supply Chain', 'url' => 'https://anaplan.seismic.com/apps/search?appType=DocCenter&keyword=Business%2520Value%2520Framework%2520(BVS)%2520for%2520Supply%2520Chain&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%20Language%3AEnglish%3BAuto-detected%20Language%3AGerman%3BAuto-detected%20Language%3ASpanish%3B&pageIndex=0&strategy=', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'SCM Transformation – First Meeting Deck', 'url' => 'https://anaplan.seismic.com/apps/search?appType=DocCenter&keyword=SCM%2520Transformation%2520%25E2%2580%2593%2520First%2520Meeting%2520Deck&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%2520Language%3AEnglish%3BAuto-detected%2520Language%3AGerman%3BAuto-detected%2520Language%3ASpanish%3B&pageIndex=0&strategy=', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Ideal Customer Profile - IBP', 'url' => 'https://anaplan.seismic.com/Link/Content/DCBQmfmHT6qhqGhBWXTQhpQFqCVG', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'IBP Pitch Deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DCfDW2D38b3FX8TDq2HRpRH27bWV', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => '02. How to sell SCP Applications', 'url' => 'https://anaplan.seismic.com/Link/Content/DCR432fT46mpM8TFbfB22h4JPq2P', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application functionality, configurations, and setup']);
        $this->addItems($o3, [
            ['title' => 'Integrated Business Planning (IBP) Configuration', 'url' => 'https://academy.anaplan.com/learn/course/393/integrated-business-planning-ibp-configuration', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Integrated Business Planning (IBP) Overview', 'url' => 'https://academy.anaplan.com/learn/course/392/integrated-business-planning-ibp-overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand dependent components']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners provisioned access to application']);
        $this->addItems($o5, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Anaplan', 'prerequisites' => 'Global IBP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation (https://applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Global IBP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners receive hands-on experience with application configuration, methodology, and extension']);
        $this->addItems($o6, [
            ['title' => 'Global IBP Deep-Dive Workshop (invitation-only)', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'App COE', 'prerequisites' => 'SC Apps GTM or Implementer Workshop', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o7 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 7, 'title' => 'Partners can Demo']);
        $this->addItems($o7, [
            ['title' => 'Conceptual Demo Assessment (read here for further instructions)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'source' => 'Anaplan', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o8 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 8, 'title' => 'Partners have completed "delivery readiness"']);
        $this->addItems($o8, [
            ['title' => 'First project is primed by PS, Partner co-prime, 
Second is primed by Partner, co-primed by PS, 
Third is primed by Partner with PS on call', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o9 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 9, 'title' => 'Partners can independently deliver']);
        $this->addItems($o9, [
            ['title' => 'Partners have co-delivered with Anaplan', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Integrated Business Planning (9 outcomes, 22 items)');
    }

    // --- Merchandise Financial Planning ─────────────────────────

    private function seedMerchandiseFinancialPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Merchandise Financial Planning', 'application', 'Supply Chain', 'V2.0',
            'Supply Chain application blueprint for merchandise financial planning in retail.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/377/anaplan-applications-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Merchandise Financial Planning (familiarize yourself with available materials)', 'url' => 'https://anaplan.seismic.com/apps/search?appType=All&keyword=Merchandise%2520Financial%2520Planning%2520MFP&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%2520Language%3AEnglish%3BAuto-detected%2520Language%3AGerman%3BAuto-detected%2520Language%3ASpanish%3B&pageIndex=0&strategy=', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '01. An Introduction to Anaplan\'s Supply Chain Planning Applications', 'url' => 'https://anaplan.seismic.com/Link/Content/DCBBX6mQXJ6288qVPBGbgdXDcCPj', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '05. Customer Stories', 'url' => 'https://anaplan.seismic.com/Link/Content/DCbj2BVmfRh9RGMVgcJD6gFCRG9V', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'MFP Positioning and Messaging', 'url' => 'https://anaplan.seismic.com/Link/Content/DC34JQHj9JBj7GMMMRfFG872VDC3', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'MFP Data Sheet', 'url' => 'https://anaplan.seismic.com/Link/Content/DCcmhjf4qG7hVGhDjF9JX494mJ7V', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Merchandise Financial Planning (MFP) App 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/414/merchandise-financial-planning-mfp-app-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Business Value Framework (BVS) for Supply Chain', 'url' => 'https://anaplan.seismic.com/apps/search?appType=DocCenter&keyword=Business%2520Value%2520Framework%2520(BVS)%2520for%2520Supply%2520Chain&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%20Language%3AEnglish%3BAuto-detected%20Language%3AGerman%3BAuto-detected%20Language%3ASpanish%3B&pageIndex=0&strategy=', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'SCM Transformation – First Meeting Deck', 'url' => 'https://anaplan.seismic.com/apps/search?appType=DocCenter&keyword=SCM%2520Transformation%2520%25E2%2580%2593%2520First%2520Meeting%2520Deck&contentType=All%20Documents&folderId=&folderName=&fromAppType=&currentTeamSiteId=&sharedTeamSiteId=&profileItemId=&selectedProperties=ContentProfiles%3APartner%3BAuto-detected%2520Language%3AEnglish%3BAuto-detected%2520Language%3AGerman%3BAuto-detected%2520Language%3ASpanish%3B&pageIndex=0&strategy=', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Ideal Customer Profile - MFP', 'url' => 'https://anaplan.seismic.com/Link/Content/DCbVbHpmGjHq68fRDpjGXCd2WTDV', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'MFP Pitch Deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DCm43Xm6Vm9hRG2JmBMPqG6TPPHd', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => '02. How to sell SCP Applications', 'url' => 'https://anaplan.seismic.com/Link/Content/DCR432fT46mpM8TFbfB22h4JPq2P', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application functionality, configurations, and setup']);
        $this->addItems($o3, [
            ['title' => 'Merchandise Financial Planning Application Configuration', 'url' => 'https://academy.anaplan.com/learn/courses/395/merchandise-financial-planning-mfp-configuration/lessons', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Merchandise Financial Planning Application Overview', 'url' => 'https://academy.anaplan.com/learn/courses/394/merchandise-financial-planning-mfp-overview/lessons', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand dependent components']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners provisioned access to application']);
        $this->addItems($o5, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Anaplan', 'prerequisites' => 'Global MFP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation (https://applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Global MFP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners receive hands-on experience with application configuration, methodology, and extension']);
        $this->addItems($o6, [
            ['title' => 'Global MFP Deep-Dive Workshop (invitation-only)', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'prerequisites' => 'SC Apps GTM or Implementer Workshop', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o7 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 7, 'title' => 'Partners can Demo']);
        $this->addItems($o7, [
            ['title' => 'Conceptual Demo Assessment (read here for further instructions)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'source' => 'Anaplan', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o8 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 8, 'title' => 'Partners have completed "delivery readiness"']);
        $this->addItems($o8, [
            ['title' => 'First project is primed by PS, Partner co-prime, 
Second is primed by Partner, co-primed by PS, 
Third is primed by Partner with PS on call', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o9 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 9, 'title' => 'Partners can independently deliver']);
        $this->addItems($o9, [
            ['title' => 'Partners have co-delivered with Anaplan', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Merchandise Financial Planning (9 outcomes, 22 items)');
    }

    // --- Integrated Financial Planning (IFP) ────────────────────

    private function seedIFP(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Integrated Financial Planning (IFP)', 'application', 'Finance', 'V2.0',
            'Finance application blueprint for integrated financial planning and budgeting.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/377/anaplan-applications-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IFP App 101 for Partners​', 'url' => 'https://academy.anaplan.com/learn/courses/356/integrated-financial-planning-ifp-app-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Integrated Financial Planning Application for Partners', 'url' => 'https://anaplan.seismic.com/apps/doccenter/f4c8d4fe-f6c0-46d8-85cc-d94645d413ce/doc/%252Fdd434fd8b9-9551-65e4-1b84-7cc32af3d43e%252FdfNjhjNWQyNGUtMzdhOC00ZDYzLWE5NzQtYmJhZTgyYTQyNjg4%252CPT0%253D%252CRW5hYmxlbWVudA%253D%253D%252Flf6c5099ce-9642-4bf4-9a69-65231c02faf6/', 'duration_display' => '2 hours', 'duration_minutes' => 120, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Transform IFP and Consolidation with Anaplan', 'url' => 'https://academy.anaplan.com/learn/courses/524/transform-ifp-and-consolidation-with-anaplan/lessons', 'duration_display' => '40 Mins', 'duration_minutes' => 40, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Sales Play Summary Finance Planning Transformation', 'url' => 'https://anaplan.seismic.com/apps/doccenter/43c4640c-56b4-4bec-ae55-5c04163ff648/doc/%25252Fdd75ddaa6f-ac28-bd54-ca4b-225fc9c6ed68%25252FdfNThiZDIxNWMtYjc3NS00ZTZiLTkwYzgtZWIxZjM4MTQ3YTUx%25252CPT0%25253D%25252CRmluYW5jZQ%25253D%25253D%25252FdfNjhjNWQyNGUtMzdhOC00ZDYzLWE5NzQtYmJhZTgyYTQyNjg4%25252CPT0%25253D%25252CUGxheWJvb2s%25253D%25252Flf1ea11615-9d8d-455a-b6ba-b6a4ae577a3a/?mode=view', 'duration_display' => '10 Mins', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Finance Planning Transformation- Drive enterprise value with connected finance', 'url' => 'https://anaplan.seismic.com/apps/doccenter/f4c8d4fe-f6c0-46d8-85cc-d94645d413ce/doc/%25252Fdd434fd8b9-9551-65e4-1b84-7cc32af3d43e%25252FdfNjhjNWQyNGUtMzdhOC00ZDYzLWE5NzQtYmJhZTgyYTQyNjg4%25252CPT0%25253D%25252CRGF0YXNoZWV0%25252Flf1f69a06c-9d4b-469b-9880-83132f1908f0/?mode=view&searchId=aaa072ee-4f4a-4abb-be5f-b3ac90a5e9dc', 'duration_display' => '20 Mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'Implementing Applications on the Application Framework', 'url' => 'https://academy.anaplan.com/learn/courses/499/implementing-applications-on-the-application-framework/lessons', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Deep Dive Enablement by Region / Country', 'duration_display' => '90 mins', 'duration_minutes' => 90, 'source' => 'App COE', 'prerequisites' => 'Delivery Learning Plan', 'gtm' => 'optional', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Regional in-person IFP Apps Implementer workshop', 'duration_display' => '2 days', 'duration_minutes' => 960, 'source' => 'PSM', 'prerequisites' => 'Delivery Learning Plan', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS / CoE (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Tenant', 'prerequisites' => 'Workshop Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'source' => 'Tenant', 'prerequisites' => 'Workshop Enablement', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Shared Folder with Additional App Documentation', 'source' => 'Sharepoint', 'prerequisites' => 'Workshop Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation (https://applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Workshop Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Integrated Financial Planning (IFP) (6 outcomes, 16 items)');
    }

    // --- Financial Planning & Consolidation (FPC) ───────────────

    private function seedFPC(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Financial Planning & Consolidation (FPC)', 'application', 'Finance', 'V2.0',
            'Finance application blueprint for financial planning and consolidation.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand FPC']);
        $this->addItems($o1, [
            ['title' => 'FPC Sales & Marketing Seismic', 'url' => 'https://anaplan.highspot.com/items/68c1c86e98d048886b5bcebf?lfrm=ssrp.1', 'duration_display' => '1 hours', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Blueprint', 'url' => 'https://anaplan.sharepoint.com/:p:/r/sites/Team-Partner-Success-Global/Shared%20Documents/11%20Application%20Blueprints/Partner%20Enablement%20Blueprints%20for%20Anaplan%20Applications%20V1.2.pptx?d=w39c1c2a3e4204872a75723d98e57a549&csf=1&web=1&e=EzSKF7&nav=eyJzSWQiOjI2MSwiY0lkIjozOTAzMDk0ODQ0fQ', 'duration_display' => '30 hours', 'duration_minutes' => 1800, 'source' => 'Various', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IFP Blueprint', 'url' => 'https://anaplan.sharepoint.com/:p:/r/sites/Team-Partner-Success-Global/Shared%20Documents/11%20Application%20Blueprints/Partner%20Enablement%20Blueprints%20for%20Anaplan%20Applications%20V1.2.pptx?d=w39c1c2a3e4204872a75723d98e57a549&csf=1&web=1&e=4XODgb&nav=eyJzSWQiOjIxNDc0ODM0NzYsImNJZCI6Mzc3NDEzODgwMH0', 'duration_display' => '10 hours', 'duration_minutes' => 600, 'source' => 'Various', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Data Orchestrator (ADO) Training', 'url' => 'https://academy.anaplan.com/learn/learning-plans/25/anaplan-data-orchestrator-ado-certification', 'duration_display' => '6 hours', 'duration_minutes' => 360, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Financial Planning & Consolidation (FPC) (1 outcomes, 4 items)');
    }

    // --- Financial Close & Reporting (FCR) ──────────────────────

    private function seedFCR(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Financial Close & Reporting (FCR)', 'application', 'Finance', 'V2.0',
            'Finance application blueprint for financial close and reporting processes.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'FCR Positioning Statement​', 'url' => 'https://anaplan.highspot.com/items/67bdb71efacb1b0f31645e92#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'url' => 'https://anaplan.highspot.com/items/67bdb89afacb1b39f0dc4358?lfrm=srp.0#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary​', 'url' => 'https://anaplan.seismic.com/Link/Content/DCHqbp788Dq3H89XXchcqGchBF6G', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Sales Play', 'url' => 'https://anaplan.highspot.com/items/67bdb58066bcb4f7f31d9735?lfrm=rhp.0', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'IFRS Accelerator GTM', 'url' => 'https://anaplan.seismic.com/Link/Content/DCpHfFD678GMFGHMJ2c7b8VXXP9V', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'Introduction to Anaplan Consolidations​', 'url' => 'https://academy.anaplan.com/learn/courses/19/introduction-to-anaplan-consolidation', 'duration_display' => '1 hours', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL Fundamentals​', 'url' => 'https://academy.anaplan.com/learn/learning-plans/3/anaplan-xl-reporting-training', 'duration_display' => '3 hours', 'duration_minutes' => 180, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Consolidations Fundamentals​', 'url' => 'https://academy.anaplan.com/learn/learning-plans/1/anaplan-consolidation-fundamentals-training', 'duration_display' => '12 hours', 'duration_minutes' => 720, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Consolidations Implementer​', 'url' => 'https://academy.anaplan.com/learn/learning-plans/2/anaplan-consolidation-implementer-training', 'duration_display' => '12 hours', 'duration_minutes' => 720, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'not applicable', 'source' => '-', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Deep-Dive Enablement by region/country', 'duration_display' => '90 mins', 'duration_minutes' => 90, 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'optional', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Regional in-person Implementer workshop', 'duration_display' => '2.5 days', 'duration_minutes' => 1200, 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision AnaplanXL, FCR', 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation', 'url' => 'https://applications.anaplan.com/financial-consolidation--reporting-7db4c09a-7c1b-444e-bece-7f5887e7f0c3', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'App COE', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Enablement Seismic Page​', 'url' => 'https://anaplan.highspot.com/items/67d0799e62561c5f0bb9a0ec?lfrm=shp.1', 'duration_display' => '4 hours', 'duration_minutes' => 240, 'source' => 'Seismic', 'prerequisites' => '2 learners completed 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'IFRS Accelerator & EMEA Demo Script / IFRS & EMEA Demo Video / AMER Demo Videos (bottom of the page)', 'url' => 'https://anaplan.highspot.com/items/67d0799e62561c5f0bb9a0ec?lfrm=shp.1', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'App COE', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $this->command->info('  Created: Financial Close & Reporting (FCR) (6 outcomes, 18 items)');
    }

    // --- AnaplanXL (AXL) ────────────────────────────────────────

    private function seedAXL(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'AnaplanXL (AXL)', 'application', 'Finance', 'V2.0',
            'Finance application blueprint for AnaplanXL reporting add-in.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'FCR Positioning Statement​', 'url' => 'https://anaplan.highspot.com/items/67bdb71efacb1b0f31645e92#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'url' => 'https://anaplan.highspot.com/items/67bdb89afacb1b39f0dc4358?lfrm=srp.0#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary​', 'url' => 'https://anaplan.seismic.com/Link/Content/DCHqbp788Dq3H89XXchcqGchBF6G', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Sales Play', 'url' => 'https://anaplan.highspot.com/items/67bdb58066bcb4f7f31d9735?lfrm=rhp.0', 'duration_display' => '1 hours', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'AnaplanXL Reporting Training', 'url' => 'https://academy.anaplan.com/learn/learning-plans/3/anaplan-xl-reporting-training', 'duration_display' => '14 hours', 'duration_minutes' => 840, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL How-to Videos', 'url' => 'https://community.anaplan.com/discussion/160603/unlocking-the-power-of-anaplan-xl-reporting-a-guide-to-using-anaplan-xl', 'duration_display' => '2 hours', 'duration_minutes' => 120, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'not applicable', 'source' => '-', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision AnaplanXL', 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Join & Participate in the Community', 'url' => 'https://community.anaplan.com/categories/add-ins-and-extensions', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Technical Documentation​', 'url' => 'https://applications.anaplan.com/financial-consolidation--reporting-7db4c09a-7c1b-444e-bece-7f5887e7f0c3', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'App COE', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Enablement Seismic Page​', 'url' => 'https://anaplan.highspot.com/items/67d0799e62561c5f0bb9a0ec?lfrm=shp.1', 'duration_display' => '4 hours', 'duration_minutes' => 240, 'source' => 'Seismic', 'prerequisites' => '2 learners completed 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: AnaplanXL (AXL) (6 outcomes, 13 items)');
    }

    // --- Anaplan Disclosure Management (ADM) ────────────────────

    private function seedADM(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Anaplan Disclosure Management (ADM)', 'application', 'Finance', 'V2.0',
            'Finance application blueprint for disclosure management and narrative reporting.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'FCR Positioning Statement​', 'url' => 'https://anaplan.highspot.com/items/67bdb71efacb1b0f31645e92#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'url' => 'https://anaplan.highspot.com/items/67bdb89afacb1b39f0dc4358?lfrm=srp.0#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary​', 'url' => 'https://anaplan.seismic.com/Link/Content/DCHqbp788Dq3H89XXchcqGchBF6G', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Sales Play', 'url' => 'https://anaplan.highspot.com/items/67bdb58066bcb4f7f31d9735?lfrm=rhp.0', 'duration_display' => '1 hours', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'AnaplanXL Fundamentals​', 'url' => 'https://academy.anaplan.com/learn/learning-plans/3/anaplan-xl-reporting-training', 'duration_display' => '3 hours', 'duration_minutes' => 180, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Disclosure Management Fundamentals', 'url' => 'https://academy.anaplan.com/learn/courses/2/anaplan-disclosure-management-fundamentals/lessons', 'duration_display' => '6 hours', 'duration_minutes' => 360, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL How-to Videos', 'url' => 'https://community.anaplan.com/discussion/160603/unlocking-the-power-of-anaplan-xl-reporting-a-guide-to-using-anaplan-xl', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'not applicable', 'source' => '-', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Hands-on Enablement with PS (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision AnaplanXL, DM', 'source' => 'App COE', 'prerequisites' => 'Delivery 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation (requires sign-up)​', 'url' => 'https://support.fluencetech.com/hc/en-us/categories/7289736321171-Fluence-Guides', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'App COE', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Enablement Seismic Page​', 'url' => 'https://anaplan.highspot.com/items/67d0799e62561c5f0bb9a0ec?lfrm=shp.1', 'duration_display' => '4 hours', 'duration_minutes' => 240, 'source' => 'Seismic', 'prerequisites' => '2 learners completed 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Anaplan Disclosure Management (ADM) (6 outcomes, 13 items)');
    }

    // --- Revenue Performance Management (RPM) ───────────────────

    private function seedRPM(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Revenue Performance Management (RPM)', 'application', 'Sales', 'V2.0',
            'Sales application blueprint for territory, quota, capacity planning, and segmentation.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/377/anaplan-applications-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Segmentation and Scoring Application 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/400/segmentation-and-scoring-application-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Go-to-Market Capacity Planning Application 101 Partners', 'url' => 'https://academy.anaplan.com/learn/courses/382/go-to-market-gtm-capacity-planning-app-101-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Territory & Quota Application 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/441/territory-quota-application-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan for Salesforce 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/475/anaplan-salesforce-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Go-to-Market Planning Applications', 'url' => 'https://anaplan.seismic.com/Link/Content/DCW29mFhmBg6HGfH66qd49Mb3J8G', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Segmentation and Scoring Pitch deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DCQD7bX9hR7FbGfRdmR7jGbhWTCj', 'duration_display' => '20mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Go-To-Market Capacity Planning Pitch deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DCq6MbgcD4cmdGWTMmXMXFHdM82V', 'duration_display' => '20mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Territory & Quota Pitch deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DCpg3fXmQDH6282VVF2HGmVPJXQ3', 'duration_display' => '20mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'RPM Configuration', 'url' => 'https://academy.anaplan.com/learn/learning-plans/51/territory-quota-planning-application/courses/380/rpm-configuration/lessons', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Segmentation and Scoring Overview', 'url' => 'https://academy.anaplan.com/learn/courses/379/segmentation-and-scoring-overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Go-to-Market Capacity Planning Overview', 'url' => 'https://academy.anaplan.com/learn/courses/381/gtm-capacity-planning-overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Territory & Quota Overview', 'url' => 'https://academy.anaplan.com/learn/learning-plans/51/territory-quota-planning-application/courses/390/territory-quota-planning-overview/lessons', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan for Salesforce: Connector Overview', 'url' => 'https://academy.anaplan.com/learn/courses/458/anaplan-for-salesforce-connector-overview', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan for Salesforce: Connector Configuration', 'url' => 'https://academy.anaplan.com/learn/courses/457/anaplan-for-salesforce-connector-configuration', 'duration_display' => '45 min', 'duration_minutes' => 45, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
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
            ['title' => 'Conceptual Demos companion assets (coming soon)', 'source' => 'Seismic', 'prerequisites' => '1 + 2', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (https://applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Revenue Performance Management (RPM) (6 outcomes, 22 items)');
    }

    // --- Operational Workforce Planning (OWP) ───────────────────

    private function seedOWP(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Operational Workforce Planning (OWP)', 'application', 'Workforce', 'V2.0',
            'Workforce application blueprint for operational workforce planning.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/377/anaplan-applications-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Operational Workforce Planning Overview', 'url' => 'https://anaplan.seismic.com/Link/Content/DC32X49TFch7j8CCgGB6cVf799p8', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Operational Workforce Planning Application Datasheet', 'url' => 'https://anaplan.seismic.com/Link/Content/DCp4Q2X9BRTC984BGhFg4hqfVbm8', 'duration_display' => '10 mins', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Operational Workforce Planning (OWP) App 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/course/357/operational-workforce-planning-owp-app-101-for-partners', 'duration_display' => '1 hr 5 min', 'duration_minutes' => 65, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Workforce Planning Transformation (WPT)', 'url' => 'https://anaplan.seismic.com/Link/Content/DC32X49TFch7j8CCgGB6cVf799p8', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'OWP Application Configuration (coming mid-May)', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o4, [
            ['title' => 'OWP Application Overview (coming end-March)', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners understand Polaris']);
        $this->addItems($o5, [
            ['title' => 'Anaplan Polaris Prerequisites', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o6, [
            ['title' => 'Deep Dive Enablement by Region / Country', 'duration_display' => '90 mins', 'duration_minutes' => 90, 'source' => 'App COE', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS / CoE (on project)', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o7 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 7, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o7, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'source' => 'Tenant', 'prerequisites' => 'Delivery 1 + 3 + 4 + 5', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Access to Conceptual demo Assets', 'source' => 'Tenant', 'prerequisites' => 'PreSales 1 + 2 + 4 + 5', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (https://applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Delivery 1 + 3 + 4 + 5', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Operational Workforce Planning (OWP) (7 outcomes, 14 items)');
    }

    // --- Forecaster ─────────────────────────────────────────────

    private function seedForecaster(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Forecaster', 'application', 'Platform', 'V2.0',
            'Forecaster enablement blueprint for AI-powered forecasting product.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand how to position the product´s value proposition']);
        $this->addItems($o1, [
            ['title' => 'Forecaster 101', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Data Sheet', 'url' => 'https://anaplan.highspot.com/items/68e575744d89ee0d350352b9', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Highpot Page', 'url' => 'https://anaplan.highspot.com/items/68f003d758505351745b3984?layout=details', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Pitch Deck', 'url' => 'https://anaplan.highspot.com/items/68e575744d89ee0d350352ae#1', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand configurations and setup']);
        $this->addItems($o2, [
            ['title' => 'Forecaster Technical Guide', 'url' => 'https://anaplan.highspot.com/items/68e7e1c767d685d71a3e7715', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Documentation on Anapedia', 'url' => 'https://help.anaplan.com/forecaster-bb892f43-a6bd-4353-8e07-4004f2495fa2', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Academy Training', 'url' => 'https://academy.anaplan.com/learn/courses/473/setting-up-anaplan-forecaster/lessons', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
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

        $this->command->info('  Created: Forecaster (4 outcomes, 11 items)');
    }

    // --- Delivery Excellence: Polaris ───────────────────────────

    private function seedDeliveryExcellencePolaris(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Delivery Excellence: Polaris', 'delivery', 'Platform', 'V2.0',
            'Delivery excellence blueprint for Polaris migration and implementation best practices.', 'draft');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand GTM Positioning and Use Cases']);
        $this->addItems($o1, [
            ['title' => 'Statement of Direction', 'url' => 'https://community.anaplan.com/discussion/160242/announcing-statement-of-direction-for-anaplan-calculation-engines', 'source' => 'Community', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Use Case Matrix', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Joint Operating Model – Delivery Readiness & Delivery Assurance', 'url' => 'https://anaplan.highspot.com/items/691dfaa04198b91e46d4dfb9?lfrm=irel.0#1', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'ACE: Event recording available! Polaris use cases & best practices', 'url' => 'https://community.anaplan.com/discussion/159484/event-recording-available-polaris-use-cases-best-practices', 'source' => 'Community', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Calculation Function Differences Between Engines', 'url' => 'https://help.anaplan.com/calculation-function-differences-between-engines-4c7a3bda-f406-49d5-a3ba-d54524fc0c22', 'source' => 'Anapedia', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners Understand Technical Foundation']);
        $this->addItems($o2, [
            ['title' => 'Polaris Primer Series: Part 1', 'url' => 'https://academy.anaplan.com/learn/learning-plans/6/polaris-primer-series-part-1', 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Anaplan Polaris', 'url' => 'https://academy.anaplan.com/learn/courses/10/anaplan-polaris', 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Polaris Technical Walkthrough Deck', 'url' => 'https://anaplan.highspot.com/items/6821f9d581f48436f931355d?lfrm=srp.3#8', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Anaplan Calculation Engines', 'url' => 'https://help.anaplan.com/anaplan-calculation-engines-06c06ade-2807-4f3d-9a6e-d69ae0e257e5', 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Polaris Resources', 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Support: Polaris (Planual)', 'url' => 'https://support.anaplan.com/polaris-c265a8ac-e457-46a7-b307-1fe62ffa4730', 'source' => 'Support', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners have Access to Product and Broader Community']);
        $this->addItems($o3, [
            ['title' => 'Partners have a Polaris test environment in their workspace', 'source' => 'PSM', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Known Product Issues / Field Fixes Log', 'url' => 'https://support.anaplan.com/known-issues-0e063577-c123-4747-a85c-12d8dfa766c6', 'source' => 'Support', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Best Practices from the Field (FAQ)', 'source' => 'PS', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Polaris – Natural Dimensionality', 'url' => 'https://community.anaplan.com/discussion/160443/anaplan-polaris-natural-dimensionality', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Unlocking the power of Polaris: A guide to efficient model building', 'url' => 'https://community.anaplan.com/discussion/160432/unlocking-the-power-of-polaris-a-guide-to-efficient-model-building', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'ACE Recording: Implementing with Polaris', 'url' => 'https://community.anaplan.com/discussion/159321/event-recording-available-implementing-with-polaris', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'ACE: Implementing Polaris A Deep Dive on Best Practice', 'url' => 'https://community.anaplan.com/discussion/157181/recording-available-implementing-polaris-a-deep-dive-on-best-practices', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Moving a Model from Classic to Polaris', 'url' => 'https://community.anaplan.com/discussion/160298/moving-a-model-from-classic-to-polaris', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'Polaris Migration Decision Tree', 'url' => 'https://anaplan.slack.com/files/U3KM2UBT8/F08GFM1HP3L/polaris_decision_tree.pdf', 'source' => 'Slack', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'Polaris Migration Checklist', 'url' => '../../../../../../../../../../../../../:w:/s/Team-CS-PSOps/EezOOdzjXzVBudfqSC4Fh1ABelhhiOPiiEB7LRK_bWUBrg?e=lrphCc&wdLOR=c4E7D13B1-8192-F940-B0E5-FD94A5FF9612', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o4, [
            ['title' => 'Regional virtual instructor led Hands-on Workshop: Part 1 & 2', 'source' => 'Product/PS/PSM', 'gtm' => 'optional', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS (on first project)', 'source' => 'PS', 'prerequisites' => 'Anaplan Led', 'gtm' => 'optional', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Virtual Office Hours w/ Product/PSM', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Product/PSM', 'gtm' => 'optional', 'pre_sales' => 'optional', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners can Sell & Jointly Deliver are prepared to Deliver Anaplan Features']);
        $this->addItems($o5, [
            ['title' => 'Sample SOW and Implementation Scoping Guide', 'source' => 'PS', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'Architecture Diagrams including Polaris & adjacent systems', 'source' => 'PS', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'Polaris Companion Model (Performance Testing Tool) & Example', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'Feedback Submission Process', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $this->command->info('  Created: Delivery Excellence: Polaris (5 outcomes, 28 items)');
    }

    // --- Delivery Excellence: ADO ───────────────────────────────

    private function seedDeliveryExcellenceADO(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Delivery Excellence: ADO', 'delivery', 'Platform', 'V2.0',
            'Delivery excellence blueprint for Anaplan Data Orchestrator implementation.', 'draft');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand GTM Positioning and Use Cases']);
        $this->addItems($o1, [
            ['title' => 'ADO Customer Facing Video', 'url' => 'https://anaplan.highspot.com/items/670576810dec3bfa9b708586', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Data Orchestrator_Data sheet', 'url' => 'https://anaplan.highspot.com/items/6705769029c310479a2179f8#1', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Data Orchestrator Partner FAQ', 'url' => 'https://anaplan.highspot.com/items/67597f82a4f971dc6383355a', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Data Orchestrator External FAQ', 'url' => 'https://anaplan.highspot.com/items/66f6a193f54703772bc27dd5?lfrm=srp.20', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Data Orchestrator_Pitch deck', 'url' => 'https://anaplan.highspot.com/items/6705766fe7961c45d352e6ea#6', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'SAP + Anaplan Better Together CoExist deck (Competitive)', 'url' => 'https://anaplan.highspot.com/items/626160c4194409bbc18653e1?lfrm=srp.15#1', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners Understand Technical Foundation']);
        $this->addItems($o2, [
            ['title' => 'Anaplan Data Orchestrator (ADO) Certification', 'url' => 'https://academy.anaplan.com/learn/learning-plans/25/anaplan-data-orchestrator-ado-certification', 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Known Product Issues / Field Fixes Log', 'url' => 'https://support.anaplan.com/known-issues-0e063577-c123-4747-a85c-12d8dfa766c6', 'source' => 'Support', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Data Orchestrator L2_Pitch deck', 'url' => 'https://anaplan.highspot.com/items/670576a1ddc39c44d1da02da#8', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Data Orchestrator', 'url' => 'https://help.anaplan.com/anaplan-data-orchestrator-c6188479-0975-455c-a642-bfaa17452ac0', 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Planual: Chapter 8: Anaplan Data Orchestrator', 'source' => 'Support', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners have Access to Product and Broader Community']);
        $this->addItems($o3, [
            ['title' => 'Partners have ADO entitlement and Data Orchestrator role', 'source' => 'PSM', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'ADO Community Articles', 'url' => 'https://support.anaplan.com/search?query=ADO', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Integrations and APIs: Community Discussions', 'url' => 'https://community.anaplan.com/categories/integrations-and-extensibility-discussions', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Data Orchestrator - SAP S4HANA connection', 'url' => 'https://anaplan.highspot.com/items/67356e1fafb43064e2dafb40', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Data Orchestrator - Snowflake (Basic auth) connection', 'url' => 'https://anaplan.highspot.com/items/67356e17378bcf5825ab6899', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Data Orchestrator - Azure Blob Storage Connection', 'url' => 'https://anaplan.highspot.com/items/67356e106d1af456a806cf31', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Anaplan Data Orchestrator - Salesforce connection', 'url' => 'https://anaplan.highspot.com/items/67356e00ef583152cb2c8675', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Anaplan Data Orchestrator - S3 Connection', 'url' => 'https://anaplan.highspot.com/items/67356df7db132e53d0da889e', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o4, [
            ['title' => 'Regional virtual instructor led Hands-on Workshop', 'duration_display' => '2 days', 'duration_minutes' => 960, 'source' => 'Product/PSM', 'prerequisites' => 'Presales 1+2', 'gtm' => 'optional', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Virtual Office Hours w/ Product/PSM', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Product/PSM', 'gtm' => 'optional', 'pre_sales' => 'optional', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners can Sell & Jointly Deliver are prepared to Deliver Anaplan Features']);
        $this->addItems($o5, [
            ['title' => 'Pricing Course for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/370/pricing-course-for-partners/lessons', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'V5 Pricing Overview for Partners', 'url' => 'https://anaplan.highspot.com/items/685ab8e745101f20c001616f?lfrm=srp.0#1', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'V5 Pricing FAQ', 'url' => 'https://anaplan.highspot.com/items/65df9275d8b079fc49b71693?lfrm=srp.1#1', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'Connector Request List', 'url' => 'https://anaplan.highspot.com/items/67bce25aee4925efa318e06b?lfrm=srp.0', 'source' => 'Slack', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $this->command->info('  Created: Delivery Excellence: ADO (5 outcomes, 25 items)');
    }

    // --- Delivery Excellence: Forecaster ────────────────────────

    private function seedDeliveryExcellenceForecaster(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Delivery Excellence: Forecaster', 'delivery', 'Platform', 'V2.0',
            'Delivery excellence blueprint for Forecaster product implementation.', 'draft');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand GTM Positioning and Use Cases']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Forecaster 101 for Partners', 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Comparison with PlanIQ', 'url' => 'https://anaplan.highspot.com/items/68f7e04f6c60b92eb1edb667?lfrm=srp.9', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Data sheet', 'url' => 'https://anaplan.highspot.com/items/68e575744d89ee0d350352b9?lfrm=srp.2#1', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Transition Partner FAQ', 'url' => 'https://anaplan.highspot.com/items/68c09a7107f887b9cd199eec?lfrm=srp.6#1', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster External FAQ', 'url' => 'https://anaplan.highspot.com/items/69033887bb0302eca11d7930?lfrm=srp.7', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Pitch deck', 'url' => 'https://anaplan.highspot.com/items/68e575744d89ee0d350352ae?lfrm=srp.0#1', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'PlanIQ →Forecaster Transition Plan - Customer Communication Deck for Partners', 'url' => 'https://anaplan.highspot.com/items/68c09a7107f887b9cd199ee1?lfrm=srp.10#1', 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners Understand Technical Foundation']);
        $this->addItems($o2, [
            ['title' => 'Partner Boost Recording', 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Setting Up Anaplan Forecaster', 'url' => 'https://academy.anaplan.com/learn/courses/473/setting-up-anaplan-forecaster', 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Forecaster Technical Guide (internal)', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'Forecaster Technical Enablement Guide (internal)', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'Known Product Issues / Field Fixes Log', 'url' => 'https://support.anaplan.com/known-issues-0e063577-c123-4747-a85c-12d8dfa766c6', 'source' => 'Support', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Anapedia: Anaplan Forecaster', 'url' => 'https://help.anaplan.com/forecaster-bb892f43-a6bd-4353-8e07-4004f2495fa2', 'source' => 'Support', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Release Notes', 'url' => 'https://help.anaplan.com/search?query=forecaster%20release', 'source' => 'Support', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners have Access to Product and Broader Community']);
        $this->addItems($o3, [
            ['title' => 'Partners have Forecaster entitlement', 'source' => 'PSM', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Community Articles', 'url' => 'https://community.anaplan.com/search?domain=discussions&scope=site&tagsOptions%5b0%5d%5bvalue%5d=1164&tagsOptions%5b0%5d%5blabel%5d=Forecaster&tagsOptions%5b0%5d%5btagCode%5d=forecaster&source=community', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => '<Seismic Page Links>', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o4, [
            ['title' => 'Regional virtual instructor led Hands-on Workshop', 'duration_display' => '90 min', 'duration_minutes' => 90, 'source' => 'Product/PSM', 'gtm' => 'optional', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Virtual Office Hours w/ Product/PSM', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Product/PSM', 'gtm' => 'optional', 'pre_sales' => 'optional', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners can Sell & Jointly Deliver are prepared to Deliver Anaplan Features']);
        $this->addItems($o5, [
            ['title' => 'Pricing Course for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/370/pricing-course-for-partners/lessons', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'V5 Pricing Overview for Partners', 'url' => 'https://anaplan.highspot.com/items/685ab8e745101f20c001616f?lfrm=srp.0#1', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
            ['title' => 'V5 Pricing FAQ', 'url' => 'https://anaplan.highspot.com/items/65df9275d8b079fc49b71693?lfrm=srp.1#1', 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'na'],
        ]);

        $this->command->info('  Created: Delivery Excellence: Forecaster (5 outcomes, 22 items)');
    }
}
