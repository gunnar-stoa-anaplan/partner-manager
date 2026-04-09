<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerPath;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerStageHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerPathEnrollmentSeeder extends Seeder
{
    /**
     * Large global firms → Strategic Implementation Partner.
     * These have dedicated sales/pre-sales, offshore delivery, co-sell motions.
     */
    private const STRATEGIC_PARTNERS = [
        'Accenture',
        'Deloitte LLP',
        'Deloitte Consulting LLP - US (HQ)',
        'Deloitte Touche Tohmatsu India LLP',
        'Deloitte AS - Norway',
        'PwC',
        'KPMG',
        'KPMG UK',
        'Genpact',
        'Wipro',
        'Tata Consultancy Services Limited',
        'Fujitsu Limited',
        'NTT DATA JAPAN CORPORATION',
        'Global EY (Non-GTM Partner)',
        'The Boston Consulting Group',
        'McKinsey & Company',
        'BearingPoint',
        'Slalom Consulting',
        'Baringa LLP',
        'ZS Associates',
        'NEC Corporation',
        'ITC Infotech',
    ];

    /**
     * Smaller specialist firms → Specialist Implementation Partner.
     * Deep niche expertise, small teams, primarily Anaplan-sourced deals.
     */
    private const SPECIALIST_PARTNERS = [
        'Spaulding Ridge',
        'Argano, LLC (Partner)',
        'Cervello, a Kearney Company',
        'Peloton Group',
        'Accordion Partners LLC',
        'Grant Thornton LLP',
        'Karta Consulting Group',
        'Tru Consulting',
        'Alpha Alternatives',
        'Valizant',
        'Fadmoor LLC',
        'TruQua, an IBM Company',
        'Columbus Consulting LLC',
        'Keyrus EPM Americas',
        'Keyrus EMEA',
        'Keyrus Global',
        'Bluecrux BV',
        'Vuealta Ltd',
        'Novus Consulting Ltd',
        'Tridant Pty Ltd',
        'Profit&',
        'Polestar Solutions and Services India',
        'Deflytics Consulting Private Limited',
        'Relanto Inc.',
        'Sempre Analytics',
        'Fortience Consulting Inc.',
        'Zooss Consulting Pty Ltd',
        'Cornerstone Management Consulting Pty Ltd',
        'LocAnalytics Consulting LLC',
        'Omniquest Inc',
        'Alpine Consulting Partners',
        'Bedford',
        'Inlumi',
    ];

    public function run(): void
    {
        $this->command->info('Creating partner path enrollments and stage history...');

        $strategicPath = PartnerPath::where('name', 'Strategic Implementation Partner')->first();
        $specialistPath = PartnerPath::where('name', 'Specialist Implementation Partner')->first();

        if (! $strategicPath && ! $specialistPath) {
            $defaultPath = PartnerPath::where('is_default', true)->first() ?? PartnerPath::first();
            if (! $defaultPath) {
                $this->command->warn('No partner paths found. Run PartnerPathSeeder first.');

                return;
            }
            // Fallback: enroll all in default path
            $strategicPath = $defaultPath;
            $specialistPath = $defaultPath;
        }

        $admin = User::whereRelation('role', 'slug', 'admin')->first();
        $users = User::all();
        $partners = Partner::all();

        $strategicNames = collect(self::STRATEGIC_PARTNERS);
        $specialistNames = collect(self::SPECIALIST_PARTNERS);

        $enrollmentCount = 0;
        $historyCount = 0;
        $strategicCount = 0;
        $specialistCount = 0;
        $unassignedCount = 0;

        foreach ($partners as $partner) {
            // Determine which path this partner belongs to
            if ($strategicPath && $strategicNames->contains($partner->name)) {
                $path = $strategicPath;
                $strategicCount++;
            } elseif ($specialistPath && $specialistNames->contains($partner->name)) {
                $path = $specialistPath;
                $specialistCount++;
            } else {
                // Remaining partners: assign based on rough heuristic
                // Partners with Japanese/Asian names or "Ltd" → often larger → Strategic
                // Others default to Specialist (smaller/niche is the safer default)
                $path = $specialistPath ?? $strategicPath;
                $unassignedCount++;
            }

            $stages = $path->stages()->orderBy('sort_order')->get();
            if ($stages->isEmpty()) {
                continue;
            }

            $psm = $users->firstWhere('id', $partner->user_id) ?? $admin;

            // Weighted stage distribution — adjusted per path type
            if ($path->id === $strategicPath?->id) {
                // Strategic: broader distribution, some reach higher stages
                $stageIndex = fake()->randomElement(array_merge(
                    array_fill(0, 3, 0), // Onboarded
                    array_fill(0, 4, 1), // Enabled
                    array_fill(0, 4, 2), // Supervised Delivery
                    array_fill(0, 3, 3), // Performing
                    array_fill(0, 2, 4), // Accelerating
                    array_fill(0, 1, 5), // Strategic
                ));
            } else {
                // Specialist: heavier early-stage, fewer at top
                $stageIndex = fake()->randomElement(array_merge(
                    array_fill(0, 4, 0), // Onboarded
                    array_fill(0, 5, 1), // Delivery Ready
                    array_fill(0, 4, 2), // Supervised Delivery
                    array_fill(0, 3, 3), // Proven
                    array_fill(0, 2, 4), // Trusted Expert
                    array_fill(0, 1, 5), // Elite Specialist
                ));
            }
            $stageIndex = min($stageIndex, $stages->count() - 1);
            $currentStage = $stages[$stageIndex];

            $enrolledAt = fake()->dateTimeBetween('-18 months', '-2 months');
            $stageEnteredAt = $stageIndex === 0
                ? $enrolledAt
                : fake()->dateTimeBetween($enrolledAt, 'now');

            // Named PSM partners are always active
            $isNamedPartner = $strategicNames->contains($partner->name) || $specialistNames->contains($partner->name);
            if ($isNamedPartner) {
                $status = 'active';
            } else {
                $status = fake()->randomElement(['active', 'active', 'active', 'active', 'paused']);
                if ($stageIndex === $stages->count() - 1 && fake()->boolean(30)) {
                    $status = 'completed';
                }
            }

            $enrollment = PartnerPathEnrollment::create([
                'partner_id' => $partner->id,
                'partner_path_id' => $path->id,
                'current_stage_id' => $currentStage->id,
                'enrolled_at' => $enrolledAt,
                'stage_entered_at' => $stageEnteredAt,
                'last_reviewed_at' => fake()->boolean(70) ? fake()->dateTimeBetween($stageEnteredAt, 'now') : null,
                'reviewed_by' => fake()->boolean(70) ? $psm?->id : null,
                'status' => $status,
                'notes' => fake()->boolean(30) ? fake()->sentence(6) : null,
            ]);
            $enrollmentCount++;

            // Generate stage history for transitions up to current stage
            $previousDate = $enrolledAt;
            for ($i = 0; $i <= $stageIndex; $i++) {
                $fromStage = $i === 0 ? null : $stages[$i - 1];
                $toStage = $stages[$i];

                $transitionDate = $i === 0
                    ? $enrolledAt
                    : fake()->dateTimeBetween($previousDate, $stageEnteredAt > $previousDate ? $stageEnteredAt : 'now');

                $gateResults = null;
                if ($i > 0) {
                    $gateResults = [
                        'gate_status' => 'passed',
                        'criteria_met' => fake()->numberBetween(3, 6),
                        'criteria_total' => fake()->numberBetween(4, 6),
                        'reviewed_at' => $transitionDate->format('Y-m-d H:i:s'),
                    ];
                }

                PartnerStageHistory::create([
                    'partner_id' => $partner->id,
                    'partner_path_id' => $path->id,
                    'from_stage_id' => $fromStage?->id,
                    'to_stage_id' => $toStage->id,
                    'transitioned_at' => $transitionDate,
                    'approved_by' => $psm?->id ?? $admin->id,
                    'gate_results' => $gateResults,
                    'notes' => $i === 0 ? 'Initial enrollment' : fake()->randomElement([
                        'All gate criteria met',
                        'Passed with strong performance',
                        'Approved after review',
                        'Gate criteria satisfied — advancing partner',
                    ]),
                ]);
                $historyCount++;

                $previousDate = $transitionDate;
            }
        }

        $this->command->info("  Strategic: {$strategicCount} | Specialist: {$specialistCount} | Other (→ Specialist): {$unassignedCount}");
        $this->command->info("  Created {$enrollmentCount} enrollments, {$historyCount} stage history records.");
    }
}
