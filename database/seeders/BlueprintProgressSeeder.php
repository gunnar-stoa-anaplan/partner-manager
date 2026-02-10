<?php

namespace Database\Seeders;

use App\Models\Blueprint;
use App\Models\BlueprintContentItem;
use App\Models\BlueprintEnrollment;
use App\Models\BlueprintProgress;
use App\Models\Contact;
use App\Models\Partner;
use App\Models\PartnerBlueprintAssignment;
use Illuminate\Database\Seeder;

class BlueprintProgressSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding blueprint assignments, enrollments & progress...');

        // Clear existing data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        BlueprintProgress::truncate();
        BlueprintEnrollment::truncate();
        PartnerBlueprintAssignment::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $blueprints = Blueprint::with('outcomes.contentItems')->get()->keyBy('id');
        $partners = Partner::with('contacts')->get();

        // ── Assignment Profiles ──
        // Each partner gets a realistic mix of blueprint assignments at varying stages.
        // Large partners (Accenture, Deloitte, PwC) get more assignments.
        // Smaller partners get 1-3 assignments.

        $assignmentProfiles = [
            // partner_id => [ [blueprint_id, role, status, enrollment_profile], ... ]
            // enrollment_profile: 'advanced' (70-100%), 'progressing' (30-70%), 'early' (5-30%), 'fresh' (0%)

            1 => [ // Accenture - major partner, heavy investment across all product areas
                // Sales (RPM) - all 3 roles
                [10, 'delivery', 'active', 'advanced'],      // RPM Delivery - nearly done
                [10, 'gtm', 'active', 'progressing'],        // RPM GTM - mid-progress
                [10, 'pre_sales', 'active', 'early'],        // RPM Pre-Sales - just started
                // Finance (IFP) - delivery + pre_sales
                [5, 'delivery', 'active', 'advanced'],       // IFP Delivery - almost complete
                [5, 'pre_sales', 'active', 'progressing'],   // IFP Pre-Sales - making progress
                [5, 'gtm', 'active', 'early'],               // IFP GTM - early stage
                // Supply Chain (IDP) - delivery + gtm
                [2, 'delivery', 'active', 'progressing'],    // IDP Delivery - mid-way
                [2, 'gtm', 'active', 'early'],               // IDP GTM - early stage
                // Workforce (OWP) - delivery + pre_sales
                [11, 'delivery', 'active', 'progressing'],   // OWP Delivery - mid-way
                [11, 'pre_sales', 'active', 'early'],        // OWP Pre-Sales - just started
                // Platform (Polaris + Forecaster)
                [1, 'delivery', 'completed', 'completed'],   // Polaris Delivery - done
                [1, 'gtm', 'active', 'progressing'],         // Polaris GTM - progressing
                [12, 'delivery', 'active', 'early'],         // Forecaster Delivery - early
            ],

            2 => [ // Deloitte - strong finance focus
                [5, 'delivery', 'active', 'advanced'],       // IFP Delivery - nearly done
                [5, 'gtm', 'active', 'progressing'],         // IFP GTM
                [7, 'delivery', 'active', 'progressing'],    // FCR Delivery
                [8, 'delivery', 'active', 'early'],          // AXL Delivery
                [1, 'delivery', 'completed', 'completed'],   // Polaris - done
            ],

            3 => [ // PwC - supply chain specialist
                [2, 'delivery', 'active', 'advanced'],       // IDP Delivery - far along
                [2, 'gtm', 'active', 'progressing'],         // IDP GTM
                [3, 'delivery', 'active', 'progressing'],    // IBP Delivery
                [4, 'delivery', 'active', 'early'],          // MFP Delivery - early
                [1, 'delivery', 'active', 'advanced'],       // Polaris Delivery
                [12, 'pre_sales', 'active', 'progressing'],  // Forecaster Pre-Sales
            ],

            4 => [ // Wolf, Gerlach and Shields
                [10, 'delivery', 'active', 'progressing'],   // RPM Delivery
                [10, 'gtm', 'active', 'early'],              // RPM GTM
                [11, 'delivery', 'active', 'early'],         // OWP Delivery
            ],

            5 => [ // Lockman, Klocko and Murazik
                [7, 'delivery', 'active', 'advanced'],       // FCR Delivery
                [9, 'delivery', 'active', 'progressing'],    // ADM Delivery
                [1, 'delivery', 'completed', 'completed'],   // Polaris - done
            ],

            6 => [ // Tromp and Sons
                [3, 'delivery', 'active', 'progressing'],    // IBP Delivery
                [3, 'pre_sales', 'active', 'early'],         // IBP Pre-Sales
                [12, 'delivery', 'active', 'fresh'],         // Forecaster - just assigned
            ],

            7 => [ // Schroeder and Sons
                [2, 'delivery', 'active', 'advanced'],       // IDP Delivery - nearly complete
                [2, 'pre_sales', 'active', 'progressing'],   // IDP Pre-Sales
                [4, 'delivery', 'active', 'early'],          // MFP Delivery
                [6, 'delivery', 'active', 'progressing'],    // FPC Delivery
            ],

            8 => [ // Schinner, Luettgen and Kilback
                [5, 'delivery', 'active', 'progressing'],    // IFP Delivery
                [8, 'delivery', 'active', 'early'],          // AXL Delivery
            ],

            9 => [ // Denesik Ltd
                [11, 'delivery', 'active', 'advanced'],      // OWP Delivery - far along
                [11, 'gtm', 'active', 'progressing'],        // OWP GTM
                [1, 'delivery', 'completed', 'completed'],   // Polaris - done
            ],

            10 => [ // Beer-Greenfelder
                [10, 'delivery', 'active', 'progressing'],   // RPM Delivery
                [7, 'delivery', 'active', 'early'],          // FCR Delivery
                [12, 'delivery', 'active', 'progressing'],   // Forecaster Delivery
            ],

            11 => [ // Kessler-Batz
                [9, 'delivery', 'active', 'advanced'],       // ADM Delivery - almost done
                [9, 'gtm', 'active', 'progressing'],         // ADM GTM
                [6, 'delivery', 'active', 'advanced'],       // FPC Delivery
            ],

            12 => [ // Howell-Weimann
                [4, 'delivery', 'active', 'progressing'],    // MFP Delivery
                [1, 'delivery', 'active', 'advanced'],       // Polaris
            ],

            13 => [ // Kihn, Nienow and Romaguera
                [3, 'delivery', 'active', 'early'],          // IBP Delivery
                [8, 'delivery', 'active', 'fresh'],          // AXL Delivery - just assigned
                [12, 'gtm', 'active', 'early'],              // Forecaster GTM
            ],
        ];

        $totalAssignments = 0;
        $totalEnrollments = 0;
        $totalProgress = 0;

        foreach ($assignmentProfiles as $partnerId => $profiles) {
            $partner = $partners->find($partnerId);
            if (!$partner) continue;

            $contacts = $partner->contacts;
            if ($contacts->isEmpty()) continue;

            foreach ($profiles as [$blueprintId, $role, $status, $progressProfile]) {
                $blueprint = $blueprints->get($blueprintId);
                if (!$blueprint) continue;

                // Create assignment
                $assignment = PartnerBlueprintAssignment::create([
                    'partner_id' => $partnerId,
                    'blueprint_id' => $blueprintId,
                    'role' => $role,
                    'assigned_by' => $partner->user_id,
                    'status' => $status,
                    'created_at' => now()->subDays(rand(14, 120)),
                ]);
                $totalAssignments++;

                // Determine how many contacts to enroll based on profile
                $enrollCount = match ($progressProfile) {
                    'completed' => min($contacts->count(), rand(3, 5)),
                    'advanced' => min($contacts->count(), rand(3, 6)),
                    'progressing' => min($contacts->count(), rand(2, 4)),
                    'early' => min($contacts->count(), rand(1, 3)),
                    'fresh' => min($contacts->count(), rand(1, 2)),
                };

                // Pick random contacts to enroll
                $enrolledContacts = $contacts->random($enrollCount);

                // Get applicable content items for this role
                $roleColumn = match ($role) {
                    'gtm' => 'gtm_applicable',
                    'pre_sales' => 'pre_sales_applicable',
                    'delivery' => 'delivery_applicable',
                };

                $applicableItems = BlueprintContentItem::whereHas('outcome', function ($q) use ($blueprintId) {
                    $q->where('blueprint_id', $blueprintId);
                })->where($roleColumn, '!=', 'na')->orderBy('id')->get();

                $applicableCount = $applicableItems->count();
                if ($applicableCount === 0) continue;

                foreach ($enrolledContacts as $contact) {
                    $enrollment = BlueprintEnrollment::create([
                        'partner_blueprint_assignment_id' => $assignment->id,
                        'contact_id' => $contact->id,
                        'enrolled_by' => $partner->user_id,
                        'created_at' => $assignment->created_at->addDays(rand(0, 7)),
                    ]);
                    $totalEnrollments++;

                    // Determine completion target for this contact
                    // Add variance per contact within the profile range
                    $targetPct = match ($progressProfile) {
                        'completed' => rand(95, 100),
                        'advanced' => rand(65, 95),
                        'progressing' => rand(30, 65),
                        'early' => rand(8, 30),
                        'fresh' => 0,
                    };

                    $itemsToComplete = (int) round($applicableCount * $targetPct / 100);

                    // Complete items in order (earlier outcomes first) for realism
                    $completedItems = $applicableItems->take($itemsToComplete);

                    foreach ($completedItems as $item) {
                        BlueprintProgress::create([
                            'blueprint_enrollment_id' => $enrollment->id,
                            'blueprint_content_item_id' => $item->id,
                            'completed' => true,
                            'completed_at' => $enrollment->created_at->addDays(rand(1, 60)),
                        ]);
                        $totalProgress++;
                    }

                    // Update enrollment status based on actual completion
                    $enrollment->updateStatus();
                }
            }

            $this->command->info("  {$partner->name}: " . count($profiles) . " assignments");
        }

        $this->command->info('');
        $this->command->info("Seeded {$totalAssignments} assignments, {$totalEnrollments} enrollments, {$totalProgress} progress records.");
    }
}
