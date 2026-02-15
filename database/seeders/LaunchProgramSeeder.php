<?php

namespace Database\Seeders;

use App\Models\LaunchProgram;
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
            'created_by' => $admin->id,
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
        $this->enrollPartners($polaris, rand(5, 7), 'active', 0.6, $accenture);

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
            'created_by' => $admin->id,
        ], [
            ['title' => 'Sign Participation Agreement', 'type' => 'document', 'is_required' => true, 'due_date' => now()->addWeeks(4)],
            ['title' => 'Assign Technical Lead', 'type' => 'task', 'is_required' => true, 'due_date' => now()->addWeeks(5)],
            ['title' => 'Complete ADO Training Module', 'type' => 'enablement', 'is_required' => true, 'due_date' => now()->addWeeks(8)],
            ['title' => 'Submit Initial Feedback', 'type' => 'feedback', 'is_required' => false, 'due_date' => now()->addWeeks(12)],
        ]);

        $this->enrollPartners($ado, rand(2, 3), 'nominated', 0.0, null);

        // ── Program 3: Draft (no enrollments) ──
        $this->createProgram([
            'name' => 'Forecaster Beta',
            'slug' => 'forecaster-beta',
            'product_id' => Product::where('slug', 'statistical-forecasting')->value('id'),
            'description' => 'Closed beta program for the next-generation Statistical Forecasting engine. Partners will test new AI-powered forecasting capabilities.',
            'status' => 'draft',
            'starts_at' => now()->addWeeks(8),
            'target_ga_date' => now()->addWeeks(30),
            'ends_at' => null,
            'max_partners' => 4,
            'created_by' => $admin->id,
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

    private function enrollPartners(LaunchProgram $program, int $count, string $defaultStatus, float $completionRate, ?Partner $mustInclude = null): void
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
            $status = $defaultStatus === 'active'
                ? collect(['accepted', 'active', 'active', 'active'])->random()
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
                if ($completionRate > 0) {
                    $toComplete = $milestones->filter(fn () => (float) rand(0, 100) / 100 < $completionRate);
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
