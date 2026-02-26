<?php

namespace Database\Seeders;

use App\Models\ActionPlan;
use App\Models\ActionPlanItem;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating action plans and items...');

        $partners = Partner::with('contacts')->get();
        $users = User::all();
        $admin = User::whereRelation('role', 'slug', 'admin')->first();
        $planCount = 0;
        $itemCount = 0;

        $planTemplates = [
            'qbr' => [
                'Accelerate pipeline generation through joint selling',
                'Improve delivery quality and on-time rates',
                'Expand certification coverage across team',
                'Increase partner-sourced deal flow',
            ],
            'partner_review' => [
                'Address engagement frequency gap',
                'Remediate scorecard decline in delivery',
                'Build out solution practice for {product}',
            ],
            'escalation' => [
                'Customer escalation remediation plan',
                'Delivery capability improvement plan',
                'Resource allocation recovery plan',
            ],
            'psm_initiative' => [
                'Launch joint marketing campaign — {product}',
                'Prepare partner for next path stage',
                'Enable partner on new product release',
                'Build executive alignment program',
            ],
            'system_recommendation' => [
                'Certification renewal program — expiring credentials',
                'Pipeline risk mitigation — low coverage',
                'Engagement re-activation — inactive partner',
            ],
            'path_review' => [
                'Gate readiness preparation plan',
                'Stage advancement acceleration plan',
            ],
        ];

        $products = ['IFP', 'SCM', 'SPM', 'Polaris', 'OWP'];
        $quarters = ['Q1 FY27', 'Q2 FY27', 'Q3 FY27', 'Q4 FY27'];

        foreach ($partners as $partner) {
            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;
            $contacts = $partner->contacts;

            // Each partner gets 3-7 action plans
            $numPlans = fake()->numberBetween(3, 7);

            for ($i = 0; $i < $numPlans; $i++) {
                $source = fake()->randomElement(array_keys($planTemplates));
                $titles = $planTemplates[$source];
                $title = str_replace('{product}', fake()->randomElement($products), fake()->randomElement($titles));

                $status = fake()->randomElement(['open', 'open', 'in_progress', 'in_progress', 'completed', 'cancelled']);
                $dueDate = fake()->dateTimeBetween('-2 months', '+4 months');
                $isPast = $dueDate < now();

                if ($isPast && in_array($status, ['open', 'in_progress'])) {
                    $status = fake()->randomElement(['completed', 'completed', 'overdue']);
                }

                $completedAt = $status === 'completed' ? fake()->dateTimeBetween('-3 months', 'now') : null;

                $ownerContact = ($contacts->isNotEmpty() && fake()->boolean(30))
                    ? $contacts->random()
                    : null;

                $plan = ActionPlan::create([
                    'partner_id' => $partner->id,
                    'title' => $title,
                    'description' => fake()->paragraph(2),
                    'source' => $source,
                    'status' => $status,
                    'priority' => fake()->randomElement(['low', 'medium', 'medium', 'high', 'critical']),
                    'owner_type' => $ownerContact ? 'partner' : 'psm',
                    'owner_user_id' => $ownerContact ? null : $psm->id,
                    'owner_contact_id' => $ownerContact?->id,
                    'due_date' => $dueDate,
                    'completed_at' => $completedAt,
                    'outcome' => $completedAt ? fake()->sentence(8) : null,
                    'created_by' => $psm->id,
                    'fiscal_quarter' => fake()->randomElement($quarters),
                ]);
                $planCount++;

                // Generate 2-5 line items per plan
                $numItems = fake()->numberBetween(2, 5);
                $itemDescriptions = [
                    'Schedule kickoff meeting with partner team',
                    'Review current metrics and set benchmarks',
                    'Identify top 3 accounts for joint pursuit',
                    'Create training schedule for certification prep',
                    'Draft joint marketing collateral',
                    'Set up weekly sync cadence',
                    'Review and update partner solution catalog',
                    'Conduct pipeline review and identify gaps',
                    'Prepare executive business review deck',
                    'Update partner path gate requirements checklist',
                    'Evaluate delivery team capacity',
                    'Document best practices from recent wins',
                    'Set up demo environment for solution showcase',
                    'Create customer reference program plan',
                ];

                $selectedDescs = fake()->randomElements($itemDescriptions, $numItems);
                foreach ($selectedDescs as $idx => $desc) {
                    $itemStatus = match ($status) {
                        'completed' => 'completed',
                        'cancelled' => fake()->randomElement(['pending', 'cancelled']),
                        'in_progress' => fake()->randomElement(['pending', 'in_progress', 'completed']),
                        default => fake()->randomElement(['pending', 'pending', 'in_progress']),
                    };

                    ActionPlanItem::create([
                        'action_plan_id' => $plan->id,
                        'description' => $desc,
                        'status' => $itemStatus,
                        'due_date' => fake()->dateTimeBetween($plan->due_date > now() ? 'now' : '-3 months', $plan->due_date),
                        'assigned_to' => fake()->randomElement([$psm->name, $ownerContact?->name ?? $psm->name, 'Partner team']),
                        'completed_at' => $itemStatus === 'completed' ? fake()->dateTimeBetween('-3 months', 'now') : null,
                        'notes' => fake()->boolean(20) ? fake()->sentence(4) : null,
                        'sort_order' => $idx,
                    ]);
                    $itemCount++;
                }
            }
        }

        $this->command->info("Created {$planCount} action plans, {$itemCount} items.");
    }
}
