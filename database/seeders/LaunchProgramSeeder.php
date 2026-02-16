<?php

namespace Database\Seeders;

use App\Models\LaunchProgram;
use App\Models\LaunchProgramDateChange;
use App\Models\LaunchProgramFeedback;
use App\Models\LaunchProgramMilestoneCompletion;
use App\Models\LaunchProgramParticipant;
use App\Models\LaunchProgramPartner;
use App\Models\Partner;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaunchProgramSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $users = User::all();

        // ── Program 1: Active program with lots of data ──
        $polaris = $this->createProgram([
            'name' => 'Polaris Launch Program',
            'slug' => 'polaris-launch-program',
            'product_id' => Product::where('slug', 'anaplan-extended-logic')->value('id'),
            'description' => 'Early access program for Anaplan Extended Logic (Polaris). Partners gain pre-GA access to product materials, enablement sessions, and co-marketing opportunities.',
            'status' => 'active',
            'starts_at' => now()->subWeeks(6),
            'target_ga_date' => now()->addWeeks(10),
            'ends_at' => now()->addWeeks(14),
            'max_partners' => 8,
            'partner_strategy' => 'include',
            'created_by' => $admin->id,
            'owner_id' => $users->where('role', 'psm')->random()?->id ?? $admin->id,
        ], [
            ['title' => 'Sign NDA', 'type' => 'document', 'is_required' => true, 'due_date' => now()->subWeeks(4)],
            ['title' => 'Complete Polaris Enablement Blueprint', 'type' => 'enablement', 'is_required' => true, 'due_date' => now()->subWeeks(2)],
            ['title' => 'Attend Launch Kickoff Session', 'type' => 'task', 'is_required' => true, 'due_date' => now()->subWeeks(5)],
            ['title' => 'Deploy Sandbox Environment', 'type' => 'task', 'is_required' => true, 'due_date' => now()->addWeeks(2)],
            ['title' => 'Submit Product Feedback', 'type' => 'feedback', 'is_required' => true, 'due_date' => now()->addWeeks(8)],
            ['title' => 'Complete Co-marketing Review', 'type' => 'document', 'is_required' => false, 'due_date' => now()->addWeeks(12)],
        ]);

        // Enroll 5-7 partners with participants and partial completions (Accenture always included)
        $accenture = Partner::where('name', 'like', '%Accenture%')->first();
        $this->enrollPartners($polaris, rand(5, 7), 'active', 0.6, $accenture, 0.3);

        // Add date change history for Polaris (GA was pushed back twice)
        LaunchProgramDateChange::create([
            'launch_program_id' => $polaris->id,
            'field' => 'target_ga_date',
            'old_date' => now()->addWeeks(6),
            'new_date' => now()->addWeeks(8),
            'reason' => 'Product team needs additional time for API stabilization.',
            'changed_by' => $admin->id,
            'created_at' => now()->subWeeks(3),
            'updated_at' => now()->subWeeks(3),
        ]);

        LaunchProgramDateChange::create([
            'launch_program_id' => $polaris->id,
            'field' => 'target_ga_date',
            'old_date' => now()->addWeeks(8),
            'new_date' => now()->addWeeks(10),
            'reason' => 'Security review findings require additional remediation before GA.',
            'changed_by' => $admin->id,
            'created_at' => now()->subWeeks(1),
            'updated_at' => now()->subWeeks(1),
        ]);

        LaunchProgramDateChange::create([
            'launch_program_id' => $polaris->id,
            'field' => 'ends_at',
            'old_date' => now()->addWeeks(12),
            'new_date' => now()->addWeeks(14),
            'reason' => 'Extended to accommodate GA date shift.',
            'changed_by' => $admin->id,
            'created_at' => now()->subWeeks(1),
            'updated_at' => now()->subWeeks(1),
        ]);

        // ── Program 2: Accepting nominations ──
        $ado = $this->createProgram([
            'name' => 'ADO Early Access',
            'slug' => 'ado-early-access',
            'product_id' => Product::where('slug', 'consolidation')->value('id'),
            'description' => 'Advance Delivery Operations early access program for top-tier delivery partners. Preview the next generation of project management and delivery tooling.',
            'status' => 'accepting_nominations',
            'starts_at' => now()->addWeeks(3),
            'target_ga_date' => now()->addWeeks(20),
            'ends_at' => now()->addWeeks(24),
            'max_partners' => 5,
            'partner_strategy' => 'include',
            'created_by' => $admin->id,
            'owner_id' => $users->where('role', 'psm')->random()?->id ?? $admin->id,
        ], [
            ['title' => 'Sign Participation Agreement', 'type' => 'document', 'is_required' => true, 'due_date' => now()->addWeeks(4)],
            ['title' => 'Assign Technical Lead', 'type' => 'task', 'is_required' => true, 'due_date' => now()->addWeeks(5)],
            ['title' => 'Complete ADO Training Module', 'type' => 'enablement', 'is_required' => true, 'due_date' => now()->addWeeks(8)],
            ['title' => 'Submit Initial Feedback', 'type' => 'feedback', 'is_required' => false, 'due_date' => now()->addWeeks(12)],
        ]);

        $this->enrollPartners($ado, rand(2, 3), 'nominated', 0.0, null);

        // ── Program 3: Active, fully completed by Accenture (no risk) ──
        $demandPlanning = $this->createProgram([
            'name' => 'Demand Planning Certification Launch',
            'slug' => 'demand-planning-certification-launch',
            'product_id' => Product::where('slug', 'demand-planning')->value('id'),
            'description' => 'Partner certification program for the updated Demand Planning module. Complete all milestones to earn delivery certification.',
            'status' => 'active',
            'starts_at' => now()->subWeeks(10),
            'target_ga_date' => now()->addWeeks(4),
            'ends_at' => now()->addWeeks(6),
            'max_partners' => 10,
            'partner_strategy' => 'include',
            'created_by' => $admin->id,
            'owner_id' => $users->where('role', 'psm')->random()?->id ?? $admin->id,
        ], [
            ['title' => 'Complete Product Overview Training', 'type' => 'enablement', 'is_required' => true, 'due_date' => now()->subWeeks(6)],
            ['title' => 'Pass Certification Exam', 'type' => 'task', 'is_required' => true, 'due_date' => now()->subWeeks(2)],
            ['title' => 'Submit Practice Model', 'type' => 'document', 'is_required' => true, 'due_date' => now()->subWeeks(1)],
            ['title' => 'Share Customer Case Study', 'type' => 'document', 'is_required' => false, 'due_date' => now()->addWeeks(3)],
        ]);

        // Enroll Accenture with 100% completion (no risk)
        $this->enrollPartners($demandPlanning, rand(3, 5), 'active', 0.7, $accenture, 1.0);

        // ── Program 4: Draft with exclusion (no launch partners) ──
        $this->createProgram([
            'name' => 'Forecaster Beta',
            'slug' => 'forecaster-beta',
            'product_id' => Product::where('slug', 'statistical-forecasting')->value('id'),
            'description' => 'Closed beta program for the next-generation Statistical Forecasting engine. Partners will test new AI-powered forecasting capabilities.',
            'status' => 'draft',
            'starts_at' => now()->addWeeks(8),
            'target_ga_date' => now()->addWeeks(30),
            'ends_at' => null,
            'max_partners' => null,
            'partner_strategy' => 'exclude',
            'exclusion_reason' => 'Internal-only beta phase. Partner access will be evaluated after initial product validation is complete.',
            'created_by' => $admin->id,
            'owner_id' => $admin->id,
        ], [
            ['title' => 'Sign Beta Agreement', 'type' => 'document', 'is_required' => true, 'due_date' => null],
            ['title' => 'Complete Pre-requisite Training', 'type' => 'enablement', 'is_required' => true, 'due_date' => null],
            ['title' => 'Configure Test Environment', 'type' => 'task', 'is_required' => true, 'due_date' => null],
        ]);
    }

    private function createProgram(array $attrs, array $milestones): LaunchProgram
    {
        $program = LaunchProgram::create($attrs);

        foreach ($milestones as $i => $ms) {
            $program->milestones()->create(array_merge($ms, [
                'sort_order' => $i + 1,
                'description' => $ms['description'] ?? null,
            ]));
        }

        return $program;
    }

    private function enrollPartners(LaunchProgram $program, int $count, string $defaultStatus, float $completionRate, ?Partner $mustInclude = null, ?float $mustIncludeCompletionRate = null): void
    {
        $partners = Partner::when($mustInclude, fn ($q) => $q->where('id', '!=', $mustInclude->id))
            ->inRandomOrder()
            ->take($mustInclude ? $count - 1 : $count)
            ->get();

        if ($mustInclude) {
            $partners->prepend($mustInclude);
        }
        $psms = User::where('role', 'psm')->get();
        $milestones = $program->milestones;
        $requiredMilestones = $milestones->where('is_required', true);
        $roles = array_keys(LaunchProgramParticipant::ROLES);

        foreach ($partners as $partner) {
            $isMustInclude = $mustInclude && $partner->id === $mustInclude->id;
            $rate = ($isMustInclude && $mustIncludeCompletionRate !== null) ? $mustIncludeCompletionRate : $completionRate;

            $status = $defaultStatus === 'active'
                ? ($isMustInclude ? 'active' : collect(['accepted', 'active', 'active', 'active'])->random())
                : $defaultStatus;

            $lpp = LaunchProgramPartner::create([
                'launch_program_id' => $program->id,
                'partner_id' => $partner->id,
                'status' => $status,
                'nominated_by' => $psms->isNotEmpty() ? $psms->random()->id : 1,
                'accepted_at' => in_array($status, ['accepted', 'active']) ? now()->subWeeks(rand(1, 4)) : null,
            ]);

            // Add 2-4 participants from this partner's contacts
            $contacts = $partner->contacts()->inRandomOrder()->take(rand(2, 4))->get();

            foreach ($contacts as $j => $contact) {
                $participant = LaunchProgramParticipant::create([
                    'launch_program_partner_id' => $lpp->id,
                    'contact_id' => $contact->id,
                    'role' => $j === 0 ? 'technical_lead' : $roles[array_rand($roles)],
                    'assigned_by' => $lpp->nominated_by,
                ]);

                // Complete milestones based on completion rate
                if ($rate > 0) {
                    $toComplete = $milestones->filter(fn () => (float) rand(0, 100) / 100 < $rate);

                    // For the must-include partner at a low rate, ensure required milestones stay incomplete
                    if ($isMustInclude && $mustIncludeCompletionRate !== null && $mustIncludeCompletionRate < 1.0) {
                        $requiredInComplete = $toComplete->where('is_required', true);
                        if ($requiredInComplete->count() === $requiredMilestones->count()) {
                            // Drop 2 required milestones so there's always a risk alert
                            $toDrop = $requiredInComplete->take(2)->pluck('id');
                            $toComplete = $toComplete->reject(fn ($ms) => $toDrop->contains($ms->id));
                        }
                    }

                    foreach ($toComplete as $milestone) {
                        LaunchProgramMilestoneCompletion::create([
                            'launch_program_participant_id' => $participant->id,
                            'launch_program_milestone_id' => $milestone->id,
                            'completed_at' => now()->subDays(rand(1, 30)),
                        ]);
                    }
                }
            }

            // Generate feedback for active enrollments
            if ($status === 'active' && $contacts->isNotEmpty()) {
                $feedbackCount = rand(1, 3);
                $types = array_keys(LaunchProgramFeedback::TYPES);
                for ($k = 0; $k < $feedbackCount; $k++) {
                    LaunchProgramFeedback::create([
                        'launch_program_partner_id' => $lpp->id,
                        'contact_id' => $contacts->random()->id,
                        'type' => $types[array_rand($types)],
                        'rating' => rand(3, 5),
                        'body' => collect([
                            'The new tooling is impressive — really looking forward to GA.',
                            'Sandbox setup was straightforward. Documentation could be more detailed.',
                            'Enablement session was well-organized and covered the key topics.',
                            'Would love to see more hands-on labs in the training materials.',
                            'Product performance in the preview environment exceeded expectations.',
                            'Integration with existing workflows needs some refinement.',
                            'Great collaboration with the product team during the feedback session.',
                        ])->random(),
                    ]);
                }
            }
        }
    }
}
