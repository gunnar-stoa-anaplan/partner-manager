<?php

namespace Database\Seeders;

use App\Models\Blueprint;
use App\Models\BlueprintContentItem;
use App\Models\BlueprintEnrollment;
use App\Models\BlueprintProgress;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlueprintAssignmentSeeder extends Seeder
{
    /**
     * Blueprint assignment profiles per product area.
     * Each entry: [blueprint_slug, role, completionMin, completionMax].
     *
     * @var array<string, list<array{string, string, float, float}>>
     */
    private const AREA_PROFILES = [
        'Finance' => [
            ['integrated-financial-planning-ifp', 'gtm', 0.45, 0.80],
            ['integrated-financial-planning-ifp', 'pre_sales', 0.40, 0.75],
            ['integrated-financial-planning-ifp', 'delivery', 0.55, 0.90],
            ['financial-close-reporting-fcr', 'gtm', 0.35, 0.70],
            ['financial-close-reporting-fcr', 'pre_sales', 0.30, 0.65],
            ['financial-close-reporting-fcr', 'delivery', 0.25, 0.60],
            ['financial-planning-consolidation-fpc', 'gtm', 0.40, 0.75],
            ['financial-planning-consolidation-fpc', 'delivery', 0.30, 0.65],
            ['anaplanxl-axl', 'gtm', 0.35, 0.70],
            ['anaplanxl-axl', 'pre_sales', 0.30, 0.65],
            ['anaplan-disclosure-management-adm', 'gtm', 0.25, 0.60],
            ['anaplan-disclosure-management-adm', 'pre_sales', 0.30, 0.65],
        ],
        'Platform' => [
            ['polaris-enablement', 'gtm', 0.45, 0.80],
            ['polaris-enablement', 'pre_sales', 0.35, 0.70],
            ['polaris-enablement', 'delivery', 0.60, 0.95],
            ['polaris-workshop', 'pre_sales', 0.40, 0.75],
            ['polaris-workshop', 'delivery', 0.50, 0.85],
            ['ado-enablement', 'gtm', 0.35, 0.70],
            ['ado-enablement', 'pre_sales', 0.30, 0.65],
            ['ado-enablement', 'delivery', 0.45, 0.80],
            ['forecaster', 'gtm', 0.35, 0.70],
            ['forecaster', 'pre_sales', 0.30, 0.65],
            ['forecaster', 'delivery', 0.20, 0.55],
        ],
        'Sales' => [
            ['revenue-performance-management-rpm', 'gtm', 0.50, 0.85],
            ['revenue-performance-management-rpm', 'pre_sales', 0.40, 0.75],
            ['revenue-performance-management-rpm', 'delivery', 0.35, 0.70],
        ],
        'Supply Chain' => [
            ['inventory-demand-planning', 'gtm', 0.40, 0.75],
            ['inventory-demand-planning', 'pre_sales', 0.30, 0.65],
            ['inventory-demand-planning', 'delivery', 0.45, 0.80],
            ['integrated-business-planning', 'gtm', 0.35, 0.70],
            ['integrated-business-planning', 'pre_sales', 0.25, 0.60],
            ['integrated-business-planning', 'delivery', 0.35, 0.70],
            ['merchandise-financial-planning', 'gtm', 0.30, 0.65],
            ['merchandise-financial-planning', 'pre_sales', 0.25, 0.60],
        ],
        'Workforce' => [
            ['operational-workforce-planning-owp', 'gtm', 0.40, 0.75],
            ['operational-workforce-planning-owp', 'pre_sales', 0.30, 0.65],
            ['operational-workforce-planning-owp', 'delivery', 0.40, 0.80],
        ],
    ];

    /**
     * Partner-specific delivery completion profiles per product area.
     * Returns [areaBoosts, useAllDeliveryProfiles].
     * areaBoosts: area => [boost, minOverride, enrollMin] or null for default behavior.
     */
    private function getPartnerDeliveryProfile(string $partnerName): array
    {
        if (str_contains($partnerName, 'Accenture')) {
            // Accenture: strong in Finance & Supply Chain, moderate in Sales, weak in Platform & Workforce
            return [
                [
                    'Finance' => [0.30, 0.90, 5],
                    'Supply Chain' => [0.30, 0.85, 5],
                    'Sales' => [0.30, 1.00, 2],
                    'Platform' => [0.05, 0.50, 2],
                    'Workforce' => [0.20, 1.00, 2],
                ],
                true,
            ];
        }

        if (str_contains($partnerName, 'Deloitte')) {
            return [
                [
                    'Finance' => [0.20, 0.75, 4],
                    'Supply Chain' => [0.15, 0.70, 4],
                    'Sales' => [0.10, 0.60, 3],
                    'Platform' => [0.05, 0.50, 2],
                    'Workforce' => [0.10, 0.60, 3],
                ],
                true,
            ];
        }

        if (str_contains($partnerName, 'PwC')) {
            return [
                [
                    'Finance' => [0.15, 0.65, 3],
                    'Supply Chain' => [0.10, 0.55, 3],
                ],
                false,
            ];
        }

        return [[], false];
    }

    public function run(): void
    {
        $this->command->info('Creating blueprint assignments, enrollments, and progress...');

        // Clear existing data
        \Schema::withoutForeignKeyConstraints(function () {
            BlueprintProgress::truncate();
            BlueprintEnrollment::truncate();
            PartnerBlueprintAssignment::truncate();
        });

        $partners = Partner::with('contacts')->get();
        $blueprints = Blueprint::with('outcomes.contentItems')->where('status', 'published')->get();
        $blueprintsBySlug = $blueprints->keyBy('slug');
        $blueprintsById = $blueprints->keyBy('id');
        $admin = User::whereRelation('role', 'slug', 'admin')->first();
        $users = User::all();

        if ($blueprints->isEmpty()) {
            $this->command->warn('No blueprints found. Run BlueprintSeeder first.');

            return;
        }

        $assignmentCount = 0;
        $enrollmentCount = 0;
        $progressCount = 0;
        $roleStats = ['gtm' => 0, 'pre_sales' => 0, 'delivery' => 0];

        foreach ($partners as $partner) {
            $contacts = $partner->contacts;
            if ($contacts->isEmpty()) {
                continue;
            }

            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;
            [$areaBoosts, $useAllDelivery] = $this->getPartnerDeliveryProfile($partner->name);

            // Every partner gets assignments across all 5 product areas
            $selectedAreas = collect(array_keys(self::AREA_PROFILES))->shuffle();

            // Give each partner a "strength" area with higher completion
            $strengthArea = $selectedAreas->first();

            foreach ($selectedAreas as $area) {
                $profiles = self::AREA_PROFILES[$area];
                $areaProfile = $areaBoosts[$area] ?? null;

                // Top partners always get all delivery profiles for every area
                if ($useAllDelivery) {
                    $deliveryProfiles = collect($profiles)->filter(fn ($p) => $p[1] === 'delivery');
                    $otherProfiles = collect($profiles)->filter(fn ($p) => $p[1] !== 'delivery');
                    $profileCount = $area === $strengthArea
                        ? $otherProfiles->count()
                        : fake()->numberBetween(2, min($otherProfiles->count(), 4));
                    $selectedProfiles = $deliveryProfiles->merge($otherProfiles->shuffle()->take($profileCount));
                } else {
                    $profileCount = $area === $strengthArea
                        ? count($profiles)
                        : fake()->numberBetween(2, min(count($profiles), 5));
                    $selectedProfiles = collect($profiles)->shuffle()->take($profileCount);
                }

                foreach ($selectedProfiles as [$blueprintSlug, $role, $completionMin, $completionMax]) {
                    $blueprint = $blueprintsBySlug->get($blueprintSlug);
                    if (! $blueprint) {
                        continue;
                    }

                    // Strength area gets a completion boost; delivery roles get area-specific partner boost
                    $boost = $area === $strengthArea ? 0.15 : 0;
                    if ($role === 'delivery' && $areaProfile) {
                        $boost += $areaProfile[0];
                        $completionMin = max($completionMin, $areaProfile[1]);
                    }
                    $minPct = min($completionMin + $boost, 0.95);
                    $maxPct = min($completionMax + $boost, 1.0);

                    $status = $maxPct >= 0.95 && fake()->boolean(20)
                        ? 'completed'
                        : 'active';

                    $assignment = PartnerBlueprintAssignment::create([
                        'partner_id' => $partner->id,
                        'blueprint_id' => $blueprint->id,
                        'role' => $role,
                        'assigned_by' => $psm->id,
                        'status' => $status,
                        'created_at' => now()->subDays(rand(14, 120)),
                    ]);
                    $assignmentCount++;
                    $roleStats[$role]++;

                    // Get applicable content items for this role
                    $roleColumn = match ($role) {
                        'gtm' => 'gtm_applicable',
                        'pre_sales' => 'pre_sales_applicable',
                        'delivery' => 'delivery_applicable',
                    };

                    $applicableItems = BlueprintContentItem::whereHas('outcome', function ($q) use ($blueprint) {
                        $q->where('blueprint_id', $blueprint->id);
                    })->where($roleColumn, '!=', 'na')->orderBy('id')->get();

                    if ($applicableItems->isEmpty()) {
                        continue;
                    }

                    // Top partners get more delivery enrollments for deeper bench strength
                    $enrollMin = ($role === 'delivery' && $areaProfile) ? $areaProfile[2] : 2;
                    $baseMin = $enrollMin;
                    $baseMax = $enrollMin + 4;
                    $enrollCount = fake()->numberBetween($baseMin, min($baseMax, $contacts->count()));
                    $enrolledContacts = $contacts->random($enrollCount);

                    foreach ($enrolledContacts as $contact) {
                        $targetPct = fake()->randomFloat(2, $minPct, $maxPct);
                        $itemsToComplete = (int) round($applicableItems->count() * $targetPct);

                        $enrollment = BlueprintEnrollment::create([
                            'partner_blueprint_assignment_id' => $assignment->id,
                            'contact_id' => $contact->id,
                            'enrolled_by' => $psm->id,
                            'created_at' => $assignment->created_at->copy()->addDays(rand(0, 7)),
                        ]);
                        $enrollmentCount++;

                        // Complete items in order for realism
                        $completedItems = $applicableItems->take($itemsToComplete);
                        foreach ($completedItems as $item) {
                            BlueprintProgress::create([
                                'blueprint_enrollment_id' => $enrollment->id,
                                'blueprint_content_item_id' => $item->id,
                                'completed' => true,
                                'completed_at' => $enrollment->created_at->copy()->addDays(rand(1, 60)),
                            ]);
                            $progressCount++;
                        }

                        $enrollment->updateStatus();
                    }
                }
            }

            $this->command->info("  {$partner->name}: {$selectedAreas->count()} areas assigned");
        }

        $this->command->info("Created {$assignmentCount} assignments, {$enrollmentCount} enrollments, {$progressCount} progress records.");
        $this->command->info("  Role breakdown: GTM/Sales={$roleStats['gtm']}, Pre-Sales={$roleStats['pre_sales']}, Delivery={$roleStats['delivery']}");
    }
}
