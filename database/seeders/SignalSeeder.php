<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Signal;
use App\Models\SignalEngineRun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SignalSeeder extends Seeder
{
    public function run(): void
    {
        $partners = Partner::all();

        if ($partners->isEmpty()) {
            $this->command->warn('No partners found — skipping SignalSeeder.');

            return;
        }

        $signalTemplates = $this->getSignalTemplates();

        foreach ($partners as $partner) {
            $count = rand(3, 8);
            $selected = collect($signalTemplates)->shuffle()->take($count);

            foreach ($selected as $template) {
                Signal::create(array_merge($template, [
                    'partner_id' => $partner->id,
                    'title' => str_replace('{partner}', $partner->name, $template['title']),
                    'description' => str_replace('{partner}', $partner->name, $template['description']),
                    'status' => $this->randomStatus(),
                    'acknowledged_by' => rand(0, 3) === 0 ? 1 : null,
                    'resolved_at' => rand(0, 5) === 0 ? now()->subDays(rand(1, 14)) : null,
                    'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                ]));
            }
        }

        $this->seedCorrelationGroups($partners);
        $this->seedEngineRuns();

        $this->command->info('Seeded '.Signal::count().' signals across '.$partners->count().' partners.');
    }

    protected function seedCorrelationGroups($partners): void
    {
        $eligiblePartners = $partners->random(min(5, $partners->count()));

        foreach ($eligiblePartners as $partner) {
            $correlationId = 'corr_'.Str::random(16);

            $partnerSignals = Signal::where('partner_id', $partner->id)
                ->active()
                ->inRandomOrder()
                ->limit(rand(2, 3))
                ->get();

            foreach ($partnerSignals as $signal) {
                $signal->update(['correlation_id' => $correlationId]);
            }

            if ($partnerSignals->count() >= 2) {
                Signal::create([
                    'partner_id' => $partner->id,
                    'channel' => 'recommendation',
                    'type' => 'compounding_risk_pattern',
                    'severity' => 'critical',
                    'title' => "Compounding risk pattern detected for {$partner->name}",
                    'description' => "Multiple risk indicators across {$partnerSignals->count()} dimensions suggest an accelerating problem requiring immediate intervention.",
                    'recommended_action' => 'Convene a cross-functional review immediately. Assign a dedicated recovery plan with clear milestones.',
                    'confidence_score' => round(rand(75, 95) / 100, 2),
                    'source_processor' => 'cross_dimension_correlation',
                    'correlation_id' => $correlationId,
                    'data' => [
                        'correlated_signal_count' => $partnerSignals->count(),
                        'channels_affected' => $partnerSignals->pluck('channel')->unique()->values()->toArray(),
                    ],
                    'status' => 'active',
                    'expires_at' => now()->addDays(5),
                    'created_at' => now()->subDays(rand(0, 5)),
                ]);
            }
        }
    }

    protected function seedEngineRuns(): void
    {
        for ($i = 14; $i >= 0; $i--) {
            $isDaily = true;
            $isWeekly = $i % 7 === 0;

            if ($isDaily) {
                SignalEngineRun::create([
                    'processor' => null,
                    'signals_created' => rand(5, 40),
                    'signals_expired' => rand(0, 10),
                    'signals_correlated' => rand(0, 5),
                    'partners_processed' => Partner::count(),
                    'duration_ms' => rand(800, 4500),
                    'status' => 'completed',
                    'created_at' => now()->subDays($i)->setTime(5, 0),
                    'updated_at' => now()->subDays($i)->setTime(5, 0)->addMilliseconds(rand(800, 4500)),
                ]);
            }

            if ($isWeekly) {
                SignalEngineRun::create([
                    'processor' => null,
                    'signals_created' => rand(10, 25),
                    'signals_expired' => rand(2, 15),
                    'signals_correlated' => rand(1, 8),
                    'partners_processed' => Partner::count(),
                    'duration_ms' => rand(3000, 12000),
                    'status' => 'completed',
                    'created_at' => now()->subDays($i)->setTime(7, 0),
                    'updated_at' => now()->subDays($i)->setTime(7, 0)->addMilliseconds(rand(3000, 12000)),
                ]);
            }
        }
    }

    protected function getSignalTemplates(): array
    {
        return [
            // Risk signals
            [
                'channel' => 'risk',
                'type' => 'delivery_capability_gap',
                'severity' => 'critical',
                'title' => 'Delivery capability gap in Healthcare for {partner}',
                'description' => '{partner} has 3 forecasted projects in Healthcare worth $1,250,000, but only 1 certified resource and 0 enablement-complete resources.',
                'recommended_action' => 'Prioritize certification for additional resources. Assign the delivery enablement blueprint for Healthcare.',
                'confidence_score' => 0.92,
                'source_processor' => 'delivery_capability',
                'data' => ['product_area' => 'Healthcare', 'opportunity_count' => 3, 'pipeline_amount' => 1250000, 'certified_resources' => 1, 'completed_resources' => 0, 'risk_level' => 'critical'],
                'expires_at' => now()->addDays(3),
            ],
            [
                'channel' => 'risk',
                'type' => 'pipeline_coverage_low',
                'severity' => 'warning',
                'title' => 'Low pipeline coverage for {partner}',
                'description' => 'Pipeline coverage ratio is 1.2x ($480,000 open vs $400,000 closed-won trailing 12m). Healthy coverage is typically 3x or higher.',
                'recommended_action' => 'Review pipeline development strategy. Consider joint demand generation activities.',
                'confidence_score' => 0.85,
                'source_processor' => 'pipeline_velocity',
                'data' => ['open_pipeline' => 480000, 'closed_won_12m' => 400000, 'coverage_ratio' => 1.2],
                'expires_at' => now()->addDays(5),
            ],
            [
                'channel' => 'risk',
                'type' => 'close_date_slip_cluster',
                'severity' => 'warning',
                'title' => '4 deals past close date for {partner}',
                'description' => '4 opportunities worth $720,000 have passed their expected close date. This pattern may indicate forecasting accuracy issues.',
                'recommended_action' => 'Review each slipping deal with the partner. Update close dates and validate pipeline accuracy.',
                'confidence_score' => 0.90,
                'source_processor' => 'pipeline_velocity',
                'data' => ['slipping_count' => 4, 'slipping_amount' => 720000],
                'expires_at' => now()->addDays(5),
            ],
            [
                'channel' => 'risk',
                'type' => 'certification_expiry_cluster',
                'severity' => 'warning',
                'title' => '5 certifications expiring soon for {partner}',
                'description' => '5 active certifications will expire within 60 days. Product areas affected: Supply Chain, Financial Planning.',
                'recommended_action' => 'Coordinate renewal scheduling with the partner to avoid delivery capability gaps.',
                'confidence_score' => 0.90,
                'source_processor' => 'enablement_momentum',
                'data' => ['expiring_count' => 5, 'product_areas' => ['Supply Chain', 'Financial Planning'], 'earliest_expiry' => now()->addDays(18)->toDateString()],
                'expires_at' => now()->addDays(7),
            ],
            // Opportunity signals
            [
                'channel' => 'opportunity',
                'type' => 'enablement_momentum_positive',
                'severity' => 'positive',
                'title' => 'Enablement momentum accelerating for {partner}',
                'description' => '6 blueprint completions in the last 30 days vs 2 in the prior period — a 200% increase.',
                'recommended_action' => 'Consider this partner for advanced enablement tracks or tier upgrade evaluation.',
                'confidence_score' => 0.78,
                'source_processor' => 'enablement_momentum',
                'data' => ['recent_completions' => 6, 'previous_completions' => 2],
                'expires_at' => now()->addDays(7),
            ],
            [
                'channel' => 'opportunity',
                'type' => 'pipeline_creation_surge',
                'severity' => 'positive',
                'title' => 'Pipeline creation surge for {partner}',
                'description' => '8 new opportunities created in the last 30 days vs 3 in the prior period — a 167% increase.',
                'recommended_action' => 'Reinforce momentum with additional enablement or executive engagement.',
                'confidence_score' => 0.80,
                'source_processor' => 'pipeline_velocity',
                'data' => ['recent_30d' => 8, 'previous_30d' => 3, 'growth_pct' => 166.7],
                'expires_at' => now()->addDays(7),
            ],
            [
                'channel' => 'opportunity',
                'type' => 'enablement_attendance_surge',
                'severity' => 'positive',
                'title' => 'Enablement attendance up 40% for {partner}',
                'description' => '7 enablement sessions attended in the last 30 days vs 5 previously — partner engagement is increasing.',
                'recommended_action' => 'Capitalize on momentum. Consider nominating for advanced programs or tier upgrade.',
                'confidence_score' => 0.75,
                'source_processor' => 'enablement_momentum',
                'data' => ['recent_attended' => 7, 'previous_attended' => 5],
                'expires_at' => now()->addDays(7),
            ],
            // Trend signals
            [
                'channel' => 'trend',
                'type' => 'health_declining_30d',
                'severity' => 'warning',
                'title' => 'Sustained health decline for {partner}',
                'description' => 'Health score dropped 12.3 points over 30 days (72.4 → 60.1). This indicates a persistent issue requiring attention.',
                'recommended_action' => 'Conduct root cause analysis across all health dimensions and schedule a strategy review.',
                'confidence_score' => 0.82,
                'source_processor' => 'health_trend',
                'data' => ['current_score' => 60.1, 'avg_30d' => 72.4, 'delta_30d' => -12.3],
                'expires_at' => now()->addDays(7),
            ],
            [
                'channel' => 'trend',
                'type' => 'health_declining_7d',
                'severity' => 'critical',
                'title' => 'Health score declining rapidly for {partner}',
                'description' => 'Health score dropped 8.5 points in 7 days (68.2 → 59.7). Current risk level: at_risk.',
                'recommended_action' => 'Review partner engagement and schedule a check-in call to identify root causes.',
                'confidence_score' => 0.88,
                'source_processor' => 'health_trend',
                'data' => ['current_score' => 59.7, 'avg_7d' => 68.2, 'delta_7d' => -8.5, 'risk_level' => 'at_risk'],
                'expires_at' => now()->addDays(3),
            ],
            [
                'channel' => 'trend',
                'type' => 'enablement_stalling',
                'severity' => 'warning',
                'title' => 'Enablement progress stalling for {partner}',
                'description' => '4 blueprint enrollments have had no progress in 30+ days. Overall completion rate: 45% (9/20).',
                'recommended_action' => 'Reach out to the partner to understand blockers and offer support for enablement completion.',
                'confidence_score' => 0.80,
                'source_processor' => 'enablement_momentum',
                'data' => ['stalled_count' => 4, 'completion_rate' => 45.0, 'total_enrollments' => 20, 'completed_enrollments' => 9],
                'expires_at' => now()->addDays(5),
            ],
            // Anomaly signals
            [
                'channel' => 'anomaly',
                'type' => 'engagement_drop',
                'severity' => 'warning',
                'title' => 'Engagement drop detected for {partner}',
                'description' => 'Only 1 interaction in the last 30 days vs a 90-day monthly average of 5.3. This is an 81% decrease.',
                'recommended_action' => 'Schedule an immediate check-in. Sudden engagement drops often precede churn or relationship issues.',
                'confidence_score' => 0.83,
                'source_processor' => 'engagement_anomaly',
                'data' => ['recent_30d_count' => 1, 'monthly_avg_90d' => 5.3, 'rolling_90d_total' => 16],
                'expires_at' => now()->addDays(5),
            ],
            [
                'channel' => 'anomaly',
                'type' => 'engagement_dormant',
                'severity' => 'critical',
                'title' => 'No engagement in 127 days for {partner}',
                'description' => 'Last interaction was 127 days ago. Extended periods without engagement may indicate relationship risk.',
                'recommended_action' => 'Initiate outreach immediately. Schedule a QBR or casual check-in to re-establish engagement.',
                'confidence_score' => 0.85,
                'source_processor' => 'engagement_anomaly',
                'data' => ['days_since_last' => 127, 'last_interaction_date' => now()->subDays(127)->toDateString()],
                'expires_at' => now()->addDays(7),
            ],
            // Recommendation signals
            [
                'channel' => 'recommendation',
                'type' => 'qbr_overdue',
                'severity' => 'info',
                'title' => 'Schedule QBR for {partner} — last one 120 days ago',
                'description' => 'The last QBR was held 120 days ago. Regular quarterly reviews are critical for maintaining alignment and momentum.',
                'recommended_action' => 'Schedule a QBR within the next 2 weeks. Prepare health scorecard and pipeline review materials.',
                'confidence_score' => 0.70,
                'source_processor' => 'cross_dimension_correlation',
                'data' => ['days_since_last_qbr' => 120],
                'expires_at' => now()->addDays(14),
            ],
            // Benchmark signals
            [
                'channel' => 'benchmark',
                'type' => 'health_below_portfolio',
                'severity' => 'warning',
                'title' => 'Health score significantly below portfolio average for {partner}',
                'description' => 'Health score of 42.5 is 35.2% below the portfolio average of 65.6.',
                'recommended_action' => 'Identify the lowest-scoring health dimensions and create targeted improvement plans.',
                'confidence_score' => 0.75,
                'source_processor' => 'benchmark',
                'data' => ['partner_score' => 42.5, 'portfolio_avg' => 65.6, 'deviation_pct' => -35.2, 'benchmark_source' => 'portfolio'],
                'expires_at' => now()->addDays(14),
            ],
            [
                'channel' => 'benchmark',
                'type' => 'certifications_below_portfolio',
                'severity' => 'warning',
                'title' => 'Certification density below portfolio average for {partner}',
                'description' => '2 active certifications vs portfolio average of 8.4. Low certification density limits delivery capability.',
                'recommended_action' => 'Create a certification roadmap for this partner. Identify key gaps by product area.',
                'confidence_score' => 0.70,
                'source_processor' => 'benchmark',
                'data' => ['partner_certs' => 2, 'portfolio_avg' => 8.4, 'deviation_pct' => -76.2, 'benchmark_source' => 'portfolio'],
                'expires_at' => now()->addDays(14),
            ],
        ];
    }

    protected function randomStatus(): string
    {
        $statuses = ['active', 'active', 'active', 'active', 'acknowledged', 'resolved', 'dismissed'];

        return $statuses[array_rand($statuses)];
    }
}
