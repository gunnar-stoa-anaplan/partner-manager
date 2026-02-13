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
            ['title' => 'Anaplan Polaris', 'url' => 'https://academy.anaplan.com/learn/courses/10/anaplan-polaris/lessons', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'PLANUAL now updated to include Polaris', 'url' => 'https://support.anaplan.com/polaris-c265a8ac-e457-46a7-b307-1fe62ffa4730', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Differences between calculation engines', 'url' => 'https://help.anaplan.com/differences-between-calculation-engines-88334098-e311-4496-b6c2-a5fecf0e3b47', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Calculation Engines — Statement of Direction: 2025', 'url' => 'https://help.anaplan.com/-anaplan-calculation-engines--statement-of-direction-2025-a68b54b9-170b-4584-867a-6b6600914a31', 'duration_display' => '10 min', 'duration_minutes' => 10, 'source' => 'Support', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'OEG Best Practice Series : Polaris', 'url' => 'https://community.anaplan.com/categories/best-practices?tagID=713', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Polaris (1 outcome, 5 items)');
    }

    // ─── Inventory & Demand Planning ───────────────────────────

    private function seedInventoryDemandPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Inventory & Demand Planning', 'application', 'Supply Chain', 'V1.3',
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

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => '03. Demand Planning and Forecasting', 'url' => 'https://anaplan.seismic.com/Link/Content/DCcj39FHFBQ6MGcDhbbcgq4Xqq8d', 'duration_display' => '1.5 hours', 'duration_minutes' => 90, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '04. Inventory Planning', 'url' => 'https://anaplan.seismic.com/Link/Content/DCRVFm7DJGBD48WGfGBMpHHPGRqP', 'duration_display' => '50 min', 'duration_minutes' => 50, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '07. Data Hub', 'url' => 'https://anaplan.seismic.com/Link/Content/DCgTCT8GHjpQVG2JVDQ89mVJTWDB', 'duration_display' => '40 min', 'duration_minutes' => 40, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '08. Implementation', 'url' => 'https://anaplan.seismic.com/Link/Content/DCgqHj4b6B7PT87Hq3G9Q8qm3WDP', 'duration_display' => '35 min', 'duration_minutes' => 35, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '09. Rough Effort Estimation', 'url' => 'https://anaplan.seismic.com/Link/Content/DCHmFX7J7b4hgGCMjmbVPVCcTdbV', 'duration_display' => '17 min', 'duration_minutes' => 17, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '10. Customization Support and Upgrade', 'url' => 'https://anaplan.seismic.com/Link/Content/DCM3d3J9JjMmQG9CjQ2FPCPgXhMV', 'duration_display' => '27 min', 'duration_minutes' => 27, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => '11. Partner Support for Implementation', 'url' => 'https://anaplan.seismic.com/Link/Content/DCJ87fVWB6cgT8CQ87TBgXpd8JDG', 'duration_display' => '9 min', 'duration_minutes' => 9, 'source' => 'Seismic', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
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
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Inventory & Demand Planning (6 outcomes)');
    }

    // ─── Integrated Business Planning ──────────────────────────

    private function seedIntegratedBusinessPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Integrated Business Planning', 'application', 'Supply Chain', 'V1.3',
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

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'Integrated Business Planning (IBP) Configuration', 'url' => 'https://academy.anaplan.com/learn/course/393/integrated-business-planning-ibp-configuration', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Integrated Business Planning (IBP) Overview', 'url' => 'https://academy.anaplan.com/learn/course/392/integrated-business-planning-ibp-overview', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
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
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Global IBP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Integrated Business Planning (6 outcomes)');
    }

    // ─── Merchandise Financial Planning ────────────────────────

    private function seedMerchandiseFinancialPlanning(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Merchandise Financial Planning', 'application', 'Supply Chain', 'V1.3',
            'Supply Chain application blueprint for retail merchandise financial planning.');

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

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'Merchandise Financial Planning Application Configuration', 'url' => 'https://academy.anaplan.com/learn/courses/395/merchandise-financial-planning-mfp-configuration/lessons', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Merchandise Financial Planning Application Overview', 'url' => 'https://academy.anaplan.com/learn/courses/394/merchandise-financial-planning-mfp-overview/lessons', 'duration_display' => '20 min', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
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
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Global MFP Deep-Dive', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: Merchandise Financial Planning (6 outcomes)');
    }

    // ─── IFP ───────────────────────────────────────────────────

    private function seedIFP(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Integrated Financial Planning (IFP)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint for integrated financial planning and analysis.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/377/anaplan-applications-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IFP App 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/356/integrated-financial-planning-ifp-app-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Finance Planning Transformation: Sales Play for Partners', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Finance Planning Transformation - Discrete Manufacturing: Sales Play for Partners', 'url' => 'https://anaplan.highspot.com/items/6787ccb9722f1f55063ab163', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'optional', 'pre_sales' => 'optional', 'delivery' => 'na'],
            ['title' => 'Financial Planning Transformation for Financial Services for Partners', 'url' => 'https://anaplan.highspot.com/items/67893ad0cd72676d506fd828?lfrm=rhp.0', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'optional', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'IFP - Data Hub Setup', 'url' => 'https://academy.anaplan.com/learn/courses/318/ifp-application-data-hub-setup', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'IFP - Configurator', 'url' => 'https://academy.anaplan.com/learn/courses/372/ifp-application-configurator', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $o4 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 4, 'title' => 'Partners understand Polaris']);
        $this->addItems($o4, [
            ['title' => 'Anaplan Polaris Prerequisites', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'duration_display' => '3.5 hours', 'duration_minutes' => 210, 'source' => 'multiple', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o5 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 5, 'title' => 'Partners Attend Live Workshops']);
        $this->addItems($o5, [
            ['title' => 'Deep Dive Enablement by Region / Country', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'duration_display' => '90 mins', 'duration_minutes' => 90, 'source' => 'App COE', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'required'],
            ['title' => 'Regional in-person IFP Apps Implementer workshop', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'duration_display' => '2 days', 'duration_minutes' => 960, 'source' => 'App COE', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Hands-on Enablement with PS / CoE (on project)', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'source' => 'PS/App COE', 'prerequisites' => 'Project Won', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demo Assessment (to come)', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'source' => 'PSM', 'prerequisites' => 'PreSales Blueprint (to come)', 'gtm' => 'na', 'pre_sales' => 'optional', 'delivery' => 'na'],
        ]);

        $o6 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 6, 'title' => 'Partners can Sell & Jointly Deliver']);
        $this->addItems($o6, [
            ['title' => 'Provision Partner Workspace & App copies in Anaplan Tenant', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'source' => 'Tenant', 'prerequisites' => 'Deep Dive Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Conceptual Demos copied into Partner Workspace', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'source' => 'Tenant', 'prerequisites' => 'Deep Dive Enablement', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Shared Folder with Additional App Documentation', 'url' => 'https://anaplan.highspot.com/items/67507258529c24a022ce4d2f?lfrm=rhp.0', 'source' => 'Sharepoint', 'prerequisites' => 'Deep Dive Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Deep Dive Enablement', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: IFP (6 outcomes)');
    }

    // ─── FPC ───────────────────────────────────────────────────

    private function seedFPC(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'FPC (Financial Planning & Consolidation)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint covering FPC, Consolidation, Disclosure Management & AnaplanXL.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand FPC']);
        $this->addItems($o1, [
            ['title' => 'FPC Sales & Marketing Highspot', 'url' => 'https://anaplan.highspot.com/items/68c1c86e98d048886b5bcebf?lfrm=ssrp.1', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Blueprint', 'url' => 'https://anaplan.sharepoint.com/:p:/r/sites/Team-Partner-Success-Global/Shared%20Documents/11%20Application%20Blueprints/Partner%20Enablement%20Blueprints%20for%20Anaplan%20Applications%20V1.2.pptx?d=w39c1c2a3e4204872a75723d98e57a549&csf=1&web=1&e=EzSKF7&nav=eyJzSWQiOjI2MSwiY0lkIjozOTAzMDk0ODQ0fQ', 'duration_display' => '30 hours', 'duration_minutes' => 1800, 'source' => 'Various', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'IFP Blueprint', 'url' => 'https://anaplan.sharepoint.com/:p:/r/sites/Team-Partner-Success-Global/Shared%20Documents/11%20Application%20Blueprints/Partner%20Enablement%20Blueprints%20for%20Anaplan%20Applications%20V1.2.pptx?d=w39c1c2a3e4204872a75723d98e57a549&csf=1&web=1&e=4XODgb&nav=eyJzSWQiOjIxNDc0ODM0NzYsImNJZCI6Mzc3NDEzODgwMH0', 'duration_display' => '10 hours', 'duration_minutes' => 600, 'source' => 'Various', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Data Orchestrator (ADO) Training', 'url' => 'https://academy.anaplan.com/learn/learning-plans/25/anaplan-data-orchestrator-ado-certification', 'duration_display' => '6 hours', 'duration_minutes' => 360, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: FPC (1 outcome)');
    }

    // ─── FCR ───────────────────────────────────────────────────

    private function seedFCR(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'FCR (Financial Close & Reporting)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint for financial close, consolidation, and reporting.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'FCR Positioning Statement', 'url' => 'https://anaplan.highspot.com/items/67bdb71efacb1b0f31645e92#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'url' => 'https://anaplan.highspot.com/items/67bdb89afacb1b39f0dc4358?lfrm=srp.0#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary', 'url' => 'https://anaplan.highspot.com/items/67bdb676facb1b0e07645eb6#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Sales Play', 'url' => 'https://anaplan.highspot.com/items/67bdb58066bcb4f7f31d9735?lfrm=rhp.0', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'IFRS Accelerator GTM', 'url' => 'https://anaplan.highspot.com/items/68b0dd5970cacb6994e6a74b#1', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'Introduction to Anaplan Consolidations', 'url' => 'https://academy.anaplan.com/learn/courses/19/introduction-to-anaplan-consolidation', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL Fundamentals', 'url' => 'https://academy.anaplan.com/learn/learning-plans/3/anaplan-xl-reporting-training', 'duration_display' => '3 hours', 'duration_minutes' => 180, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Consolidations Fundamentals', 'url' => 'https://academy.anaplan.com/learn/learning-plans/1/anaplan-consolidation-fundamentals-training', 'duration_display' => '12 hours', 'duration_minutes' => 720, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Consolidations Implementer', 'url' => 'https://academy.anaplan.com/learn/learning-plans/2/anaplan-consolidation-implementer-training', 'duration_display' => '12 hours', 'duration_minutes' => 720, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
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
            ['title' => 'Technical Documentation', 'url' => 'https://applications.anaplan.com/financial-consolidation--reporting-7db4c09a-7c1b-444e-bece-7f5887e7f0c3', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'App COE', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Enablement Highspot Page', 'url' => 'https://anaplan.highspot.com/items/67d0799e62561c5f0bb9a0ec?lfrm=shp.1', 'duration_display' => '4 hours', 'duration_minutes' => 240, 'source' => 'Highspot', 'prerequisites' => '2 learners completed 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'IFRS Accelerator & EMEA Demo Script / Demo Videos', 'url' => 'https://anaplan.highspot.com/items/68b0dd5970cacb6994e6a74b#1', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'App COE', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $this->command->info('  Created: FCR (6 outcomes)');
    }

    // ─── AXL ───────────────────────────────────────────────────

    private function seedAXL(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'AXL (AnaplanXL)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint for AnaplanXL reporting and analytics.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'FCR Positioning Statement', 'url' => 'https://anaplan.seismic.com/Link/Content/DC6b8TXJ6pChJGQMTVXXhb98bff8', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'url' => 'https://anaplan.seismic.com/Link/Content/DCb3pHbV87XVj8CR6QgGXmjQBHpd', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary', 'url' => 'https://anaplan.seismic.com/Link/Content/DCHqbp788Dq3H89XXchcqGchBF6G', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Pitch Deck & ICP', 'url' => 'https://anaplan.seismic.com/Link/Content/DC4mHmpX6Rf88GWQ864Q4PG7Fg7d', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'AnaplanXL Reporting Training', 'url' => 'https://academy.anaplan.com/learn/learning-plans/3/anaplan-xl-reporting-training', 'duration_display' => '14 hours', 'duration_minutes' => 840, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL How-to Videos', 'url' => 'https://community.anaplan.com/discussion/160603/unlocking-the-power-of-anaplan-xl-reporting-a-guide-to-using-anaplan-xl', 'duration_display' => '2 hours', 'duration_minutes' => 120, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
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
            ['title' => 'Join & Participate in the Community', 'url' => 'https://community.anaplan.com/categories/add-ins-and-extensions', 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Technical Documentation', 'url' => 'https://applications.anaplan.com/financial-consolidation--reporting-7db4c09a-7c1b-444e-bece-7f5887e7f0c3', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'App COE', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'FCR Enablement Seismic Page', 'url' => 'https://anaplan.seismic.com/Link/Content/DCPgqM4VdhQD2GFQb47864PXMT9j', 'duration_display' => '4 hours', 'duration_minutes' => 240, 'source' => 'Seismic', 'prerequisites' => '2 learners completed 1+3', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: AXL (6 outcomes)');
    }

    // ─── ADM ───────────────────────────────────────────────────

    private function seedADM(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'ADM (Anaplan Disclosure Management)', 'application', 'Finance', 'V1.3',
            'Finance application blueprint for disclosure management and regulatory reporting.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Data Sheet', 'url' => 'https://anaplan.seismic.com/Link/Content/DCFQb9qj2bPHF8CTcj337pQFP6g8', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'FCR Solution use Cases', 'url' => 'https://anaplan.seismic.com/Link/Content/DCb3pHbV87XVj8CR6QgGXmjQBHpd', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'FCR Sales Play Summary', 'url' => 'https://anaplan.seismic.com/Link/Content/DCHqbp788Dq3H89XXchcqGchBF6G', 'duration_display' => '15 min', 'duration_minutes' => 15, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Pitch Deck & ICP', 'url' => 'https://anaplan.seismic.com/Link/Content/DC8FfF7V9BjFM89GqBM7J3TDpQXG', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Highspot', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'AnaplanXL Fundamentals', 'url' => 'https://academy.anaplan.com/learn/learning-plans/3/anaplan-xl-reporting-training', 'duration_display' => '3 hours', 'duration_minutes' => 180, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Disclosure Management Fundamentals', 'url' => 'https://academy.anaplan.com/learn/courses/2/anaplan-disclosure-management-fundamentals/lessons', 'duration_display' => '6 hours', 'duration_minutes' => 360, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'AnaplanXL How-to Videos', 'url' => 'https://community.anaplan.com/discussion/160603/unlocking-the-power-of-anaplan-xl-reporting-a-guide-to-using-anaplan-xl', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Community', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
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
            ['title' => 'Technical Documentation', 'url' => 'https://applications.anaplan.com/finance-71457e60-55c5-4f40-a489-ef6180d0de87', 'duration_display' => '5 min', 'duration_minutes' => 5, 'source' => 'Anapedia', 'prerequisites' => 'Provisioning', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $this->command->info('  Created: ADM (6 outcomes)');
    }

    // ─── RPM ───────────────────────────────────────────────────

    private function seedRPM(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Revenue Performance Management (RPM)', 'application', 'Sales', 'V1.3',
            'Sales application blueprint covering segmentation, capacity planning, territory & quota, and Salesforce integration.');

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
            ['title' => 'Segmentation and Scoring Pitch deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DCQD7bX9hR7FbGfRdmR7jGbhWTCj', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Go-To-Market Capacity Planning Pitch deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DCq6MbgcD4cmdGWTMmXMXFHdM82V', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Territory & Quota Pitch deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DC6FBGgT8X8G9GcPpBmjJGbgfFqd', 'duration_display' => '20 mins', 'duration_minutes' => 20, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
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
            ['title' => 'Conceptual Demos companion assets (coming soon)', 'source' => 'Highspot', 'prerequisites' => '1 + 2', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'na'],
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Delivery 1 + 3 + 4', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: RPM (6 outcomes)');
    }

    // ─── OWP v2 ────────────────────────────────────────────────

    private function seedOWP(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Operational Workforce Planning (OWP v2)', 'application', 'Workforce', 'V1.3',
            'Workforce application blueprint for operational workforce planning.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand App Strategy & Positioning']);
        $this->addItems($o1, [
            ['title' => 'Anaplan Applications 101 for Partners', 'url' => 'https://academy.anaplan.com/learn/courses/377/anaplan-applications-101-for-partners', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Operational Workforce Planning Overview', 'url' => 'https://anaplan.seismic.com/Link/Content/DC32X49TFch7j8CCgGB6cVf799p8', 'duration_display' => '30 min', 'duration_minutes' => 30, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Anaplan Operational Workforce Planning Application Datasheet', 'url' => 'https://anaplan.seismic.com/Link/Content/DCp4Q2X9BRTC984BGhFg4hqfVbm8', 'duration_display' => '10 mins', 'duration_minutes' => 10, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Operational Workforce Planning (OWP) App 101 for Partners (coming mid-Feb)', 'url' => 'https://academy.anaplan.com/learn/courses/357/operational-workforce-planning-owp-app-101-for-partners/lessons', 'duration_display' => '1 hr 5 min', 'duration_minutes' => 65, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand Sales Plays & GTM Strategies']);
        $this->addItems($o2, [
            ['title' => 'Workforce Planning Transformation (WPT)', 'url' => 'https://anaplan.seismic.com/Link/Content/DC32X49TFch7j8CCgGB6cVf799p8', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o3 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 3, 'title' => 'Partners understand application configurations and setup']);
        $this->addItems($o3, [
            ['title' => 'OWP Application Initialization (coming mid-Feb)', 'url' => 'https://academy.anaplan.com/learn/courses/318/ifp-application-data-hub-setup', 'duration_display' => '15 mins', 'duration_minutes' => 15, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
            ['title' => 'Operational Workforce Planning Application Overview (coming mid-Feb)', 'url' => 'https://academy.anaplan.com/learn/courses/318/ifp-application-data-hub-setup', 'duration_display' => '30 mins', 'duration_minutes' => 30, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
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
            ['title' => 'Technical Documentation (applications.anaplan.com)', 'url' => 'https://applications.anaplan.com/', 'source' => 'Anapedia', 'prerequisites' => 'Delivery 1 + 3 + 4 + 5', 'gtm' => 'na', 'pre_sales' => 'na', 'delivery' => 'required'],
        ]);

        $this->command->info('  Created: OWP v2 (6 outcomes)');
    }

    // ─── Forecaster ────────────────────────────────────────────

    private function seedForecaster(int $adminId): void
    {
        $bp = $this->createBlueprint($adminId, 'Forecaster', 'product', 'Platform', 'V1.0',
            'Product enablement blueprint for Anaplan Forecaster — predictive analytics and forecasting.');

        $o1 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 1, 'title' => 'Partners understand how to position the product\'s value proposition']);
        $this->addItems($o1, [
            ['title' => 'Forecaster 101', 'url' => 'https://anaplan.seismic.com/Link/Content/DC2TQD2WfWbqC8FJjFRm94dMqWqG', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Data Sheet', 'url' => 'https://anaplan.seismic.com/Link/Content/DC2TQD2WfWbqC8FJjFRm94dMqWqG', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'required'],
            ['title' => 'Forecaster Pitch Deck', 'url' => 'https://anaplan.seismic.com/Link/Content/DCXh3BjqCbGBfGQJWf8q8jqcTmjj', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Seismic', 'gtm' => 'required', 'pre_sales' => 'required', 'delivery' => 'na'],
        ]);

        $o2 = BlueprintOutcome::create(['blueprint_id' => $bp->id, 'sort_order' => 2, 'title' => 'Partners understand configurations and setup']);
        $this->addItems($o2, [
            ['title' => 'Forecaster Technical Guide', 'url' => 'https://anaplan.seismic.com/Link/Content/DCgPBTfJHG3f282TFJ8cjfphH9qj', 'duration_display' => '1 hour', 'duration_minutes' => 60, 'source' => 'Academy', 'gtm' => 'na', 'pre_sales' => 'required', 'delivery' => 'required'],
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

        $this->command->info('  Created: Forecaster (4 outcomes)');
    }
}
