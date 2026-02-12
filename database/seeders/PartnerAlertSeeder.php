<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerAlert;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerAlertSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating partner alerts...');

        $partners = Partner::all();
        $users = User::all();
        $count = 0;

        $alertData = [
            'certification_expiry' => [
                'severities' => ['warning', 'warning', 'critical'],
                'titles' => ['{count} certifications expiring within 30 days', 'Model Builder certification expiry — {contact}', 'Critical certification gap — SA coverage below threshold'],
                'descriptions' => ['Partner has certifications approaching expiry that need renewal attention.'],
                'actions' => ['Schedule certification renewal planning session', 'Enroll contacts in next available exam window'],
            ],
            'pipeline_risk' => [
                'severities' => ['warning', 'critical'],
                'titles' => ['Pipeline coverage below 2x target', 'No active pipeline — partner at risk', 'Declining pipeline trend — 3 consecutive months'],
                'descriptions' => ['Pipeline metrics indicate risk to revenue targets.'],
                'actions' => ['Review pipeline together and identify new opportunities', 'Schedule joint sales planning session'],
            ],
            'delivery_risk' => [
                'severities' => ['warning', 'critical'],
                'titles' => ['Project delivery delays on {count} active projects', 'Customer satisfaction below threshold', 'Multiple support escalations this quarter'],
                'descriptions' => ['Delivery quality metrics are trending below acceptable thresholds.'],
                'actions' => ['Conduct delivery quality review', 'Assign delivery mentor from Anaplan team'],
            ],
            'engagement_gap' => [
                'severities' => ['info', 'warning'],
                'titles' => ['No interaction in 30+ days', 'Declining meeting attendance', 'Partner unresponsive to recent outreach'],
                'descriptions' => ['Engagement frequency has dropped below expected cadence.'],
                'actions' => ['Schedule check-in call', 'Reach out to partner executive sponsor'],
            ],
            'goal_gap' => [
                'severities' => ['warning', 'critical'],
                'titles' => ['YTD sourced revenue at {pct}% of target', 'Goal dimension gap — certifications behind plan', 'Multiple goal dimensions off-track'],
                'descriptions' => ['Partner is trending behind on one or more goal dimensions.'],
                'actions' => ['Review goals and create catch-up action plan', 'Adjust targets or create accelerated plan'],
            ],
            'task_overdue' => [
                'severities' => ['info', 'warning'],
                'titles' => ['{count} tasks overdue', 'Critical path task overdue — gate readiness blocked', 'Required task stalled for 14+ days'],
                'descriptions' => ['Partner has overdue tasks requiring attention.'],
                'actions' => ['Review and reprioritize overdue tasks', 'Reassign or adjust timelines'],
            ],
            'path_gate_ready' => [
                'severities' => ['positive'],
                'titles' => ['Partner meets gate criteria for next stage', 'All gate requirements satisfied — ready to advance'],
                'descriptions' => ['Partner has met the requirements for path advancement.'],
                'actions' => ['Schedule gate review meeting', 'Prepare advancement recommendation'],
            ],
            'path_gate_blocked' => [
                'severities' => ['warning'],
                'titles' => ['Gate advancement blocked — {count} criteria unmet', 'Stalled at current stage for 90+ days'],
                'descriptions' => ['Partner is blocked from path advancement due to unmet gate criteria.'],
                'actions' => ['Review blocking criteria and create remediation plan', 'Schedule path review with partner'],
            ],
            'scorecard_decline' => [
                'severities' => ['warning', 'critical'],
                'titles' => ['Overall score dropped {points} points', 'Scorecard tier downgrade risk', 'Delivery score declined below gate threshold'],
                'descriptions' => ['Partner scorecard has shown a significant decline.'],
                'actions' => ['Conduct scorecard deep-dive with partner', 'Create improvement action plan'],
            ],
            'case_escalation' => [
                'severities' => ['critical'],
                'titles' => ['Critical case escalation — {subject}', 'Multiple case escalations this month'],
                'descriptions' => ['A support case has been escalated requiring PSM attention.'],
                'actions' => ['Review escalated case and engage resolution team', 'Schedule customer bridge call'],
            ],
            'stale_partner' => [
                'severities' => ['info', 'warning'],
                'titles' => ['Partner inactive for 60+ days', 'No pipeline or engagement activity in current quarter'],
                'descriptions' => ['Partner shows minimal recent activity.'],
                'actions' => ['Evaluate partner re-engagement strategy', 'Schedule executive re-engagement meeting'],
            ],
            'system' => [
                'severities' => ['info'],
                'titles' => ['New data import completed', 'Scorecard auto-refresh completed', 'Quarterly goal refresh reminder'],
                'descriptions' => ['System-generated informational alert.'],
                'actions' => ['Review imported data for accuracy', 'No action required — informational'],
            ],
        ];

        foreach ($partners as $partner) {
            // Each partner gets 2-6 alerts
            $alertCount = fake()->numberBetween(2, 6);
            $selectedTypes = fake()->randomElements(array_keys($alertData), min($alertCount, count($alertData)));

            foreach ($selectedTypes as $type) {
                $data = $alertData[$type];
                $severity = fake()->randomElement($data['severities']);

                $title = fake()->randomElement($data['titles']);
                $title = str_replace(
                    ['{count}', '{contact}', '{pct}', '{points}', '{subject}'],
                    [fake()->numberBetween(2, 8), fake()->name(), fake()->numberBetween(30, 70), fake()->numberBetween(5, 15), fake()->sentence(3)],
                    $title
                );

                $status = fake()->randomElement(['active', 'active', 'active', 'acknowledged', 'resolved', 'dismissed']);
                $acknowledgedBy = in_array($status, ['acknowledged', 'resolved', 'dismissed'])
                    ? $users->random()->id
                    : null;

                PartnerAlert::create([
                    'partner_id' => $partner->id,
                    'type' => $type,
                    'severity' => $severity,
                    'title' => $title,
                    'description' => fake()->randomElement($data['descriptions']),
                    'recommended_action' => fake()->randomElement($data['actions']),
                    'data' => [
                        'generated_at' => now()->toIso8601String(),
                        'source' => 'seeder',
                        'metrics' => [
                            'threshold' => fake()->numberBetween(50, 80),
                            'actual' => fake()->numberBetween(20, 90),
                        ],
                    ],
                    'status' => $status,
                    'acknowledged_by' => $acknowledgedBy,
                    'resolved_at' => $status === 'resolved' ? fake()->dateTimeBetween('-1 month', 'now') : null,
                    'auto_resolve_when' => fake()->boolean(20) ? 'metric_threshold_met' : null,
                ]);

                $count++;
            }
        }

        $this->command->info("Created {$count} alerts.");
    }
}
