<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\BlueprintEnrollment;
use App\Models\Certification;
use App\Models\EnablementRegistration;
use App\Models\Partner;
use Illuminate\Support\Collection;

class EnablementMomentumProcessor implements SignalProcessor
{
    public function name(): string
    {
        return 'enablement_momentum';
    }

    public function channel(): string
    {
        return 'trend';
    }

    public function cadence(): string
    {
        return 'daily';
    }

    public function describe(): array
    {
        return [
            'description' => 'Tracks blueprint enrollment progress, certification expiry pipeline, and enablement event attendance. Detects stalling, acceleration, and attendance drops or surges.',
            'data_sources' => ['BlueprintEnrollment (completion rates, stalled enrollments)', 'Certification (expiring within 60 days)', 'EnablementRegistration (attendance counts)'],
            'signal_types' => [
                'enablement_stalling' => [
                    'severity' => 'warning',
                    'description' => '≥ 3 blueprint enrollments with no progress in 30+ days.',
                ],
                'enablement_momentum_positive' => [
                    'severity' => 'positive',
                    'description' => 'Blueprint completions in last 30 days exceed prior period by ≥ 50%.',
                ],
                'certification_expiry_cluster' => [
                    'severity' => '≥ 5 expiring → critical, ≥ 3 → warning',
                    'description' => 'Multiple certifications expiring within 60 days.',
                ],
                'enablement_attendance_drop' => [
                    'severity' => 'warning',
                    'description' => 'Enablement attendance dropped to ≤ 30% of the prior 30-day period.',
                ],
                'enablement_attendance_surge' => [
                    'severity' => 'positive',
                    'description' => 'Enablement attendance exceeds the prior period by ≥ 50%.',
                ],
            ],
            'thresholds' => [
                'stalled_threshold' => '≥ 3 in-progress enrollments with no update in 30 days',
                'momentum_threshold' => '> 150% of prior period completions',
                'cert_expiry_warning' => '≥ 3 certs expiring within 60 days',
                'cert_expiry_critical' => '≥ 5 certs expiring within 60 days',
                'attendance_drop' => '≤ 30% of prior period (min 3 prior)',
                'attendance_surge' => '> 150% of prior period',
                'expiry' => '5-7 days',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $signals = collect();

        $this->checkBlueprintCompletion($partner, $signals);
        $this->checkCertificationPipeline($partner, $signals);
        $this->checkEnablementAttendance($partner, $signals);

        return $signals;
    }

    protected function checkBlueprintCompletion(Partner $partner, Collection $signals): void
    {
        $contactIds = $partner->contacts()->pluck('id');

        if ($contactIds->isEmpty()) {
            return;
        }

        $totalEnrollments = BlueprintEnrollment::whereIn('contact_id', $contactIds)->count();
        $completedEnrollments = BlueprintEnrollment::whereIn('contact_id', $contactIds)
            ->where('status', 'completed')
            ->count();

        $recentCompletions = BlueprintEnrollment::whereIn('contact_id', $contactIds)
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(30))
            ->count();

        $previousCompletions = BlueprintEnrollment::whereIn('contact_id', $contactIds)
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(60))
            ->where('completed_at', '<', now()->subDays(30))
            ->count();

        if ($totalEnrollments > 0) {
            $completionRate = ($completedEnrollments / $totalEnrollments) * 100;

            $stalledEnrollments = BlueprintEnrollment::whereIn('contact_id', $contactIds)
                ->where('status', 'in_progress')
                ->where('updated_at', '<', now()->subDays(30))
                ->count();

            if ($stalledEnrollments >= 3) {
                $signals->push([
                    'channel' => 'trend',
                    'type' => 'enablement_stalling',
                    'severity' => 'warning',
                    'title' => "Enablement progress stalling for {$partner->name}",
                    'description' => sprintf(
                        '%d blueprint enrollments have had no progress in 30+ days. Overall completion rate: %.0f%% (%d/%d).',
                        $stalledEnrollments,
                        $completionRate,
                        $completedEnrollments,
                        $totalEnrollments
                    ),
                    'recommended_action' => 'Reach out to the partner to understand blockers and offer support for enablement completion.',
                    'confidence_score' => 0.80,
                    'source_processor' => 'enablement_momentum',
                    'data' => [
                        'stalled_count' => $stalledEnrollments,
                        'completion_rate' => round($completionRate, 1),
                        'total_enrollments' => $totalEnrollments,
                        'completed_enrollments' => $completedEnrollments,
                    ],
                    'expires_at' => now()->addDays(5),
                ]);
            }
        }

        if ($previousCompletions > 0 && $recentCompletions > $previousCompletions * 1.5) {
            $signals->push([
                'channel' => 'opportunity',
                'type' => 'enablement_momentum_positive',
                'severity' => 'positive',
                'title' => "Enablement momentum accelerating for {$partner->name}",
                'description' => sprintf(
                    '%d blueprint completions in the last 30 days vs %d in the prior period — a %.0f%% increase.',
                    $recentCompletions,
                    $previousCompletions,
                    (($recentCompletions - $previousCompletions) / $previousCompletions) * 100
                ),
                'recommended_action' => 'Consider this partner for advanced enablement tracks or tier upgrade.',
                'confidence_score' => 0.75,
                'source_processor' => 'enablement_momentum',
                'data' => [
                    'recent_completions' => $recentCompletions,
                    'previous_completions' => $previousCompletions,
                ],
                'expires_at' => now()->addDays(7),
            ]);
        }
    }

    protected function checkCertificationPipeline(Partner $partner, Collection $signals): void
    {
        $expiringCerts = Certification::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(60))
            ->where('expiry_date', '>', now())
            ->get();

        if ($expiringCerts->count() >= 3) {
            $signals->push([
                'channel' => 'risk',
                'type' => 'certification_expiry_cluster',
                'severity' => $expiringCerts->count() >= 5 ? 'critical' : 'warning',
                'title' => "{$expiringCerts->count()} certifications expiring soon for {$partner->name}",
                'description' => sprintf(
                    '%d active certifications will expire within 60 days. Product areas affected: %s.',
                    $expiringCerts->count(),
                    $expiringCerts->pluck('product_area')->filter()->unique()->implode(', ') ?: 'various'
                ),
                'recommended_action' => 'Coordinate renewal scheduling with the partner to avoid delivery capability gaps.',
                'confidence_score' => 0.90,
                'source_processor' => 'enablement_momentum',
                'data' => [
                    'expiring_count' => $expiringCerts->count(),
                    'product_areas' => $expiringCerts->pluck('product_area')->filter()->unique()->values()->toArray(),
                    'earliest_expiry' => $expiringCerts->min('expiry_date')?->toDateString(),
                ],
                'expires_at' => now()->addDays(7),
            ]);
        }
    }

    protected function checkEnablementAttendance(Partner $partner, Collection $signals): void
    {
        $recentAttended = EnablementRegistration::where('partner_id', $partner->id)
            ->where('status', 'attended')
            ->where('attended_at', '>=', now()->subDays(30))
            ->count();

        $previousAttended = EnablementRegistration::where('partner_id', $partner->id)
            ->where('status', 'attended')
            ->where('attended_at', '>=', now()->subDays(60))
            ->where('attended_at', '<', now()->subDays(30))
            ->count();

        if ($previousAttended >= 3 && $recentAttended <= $previousAttended * 0.3) {
            $signals->push([
                'channel' => 'trend',
                'type' => 'enablement_attendance_drop',
                'severity' => 'warning',
                'title' => "Enablement attendance drop for {$partner->name}",
                'description' => sprintf(
                    'Only %d enablement sessions attended in the last 30 days vs %d in the prior period — a %.0f%% decrease.',
                    $recentAttended,
                    $previousAttended,
                    ((1 - ($recentAttended / $previousAttended)) * 100)
                ),
                'recommended_action' => 'Investigate whether enablement content is meeting partner needs. Consider scheduling a feedback session.',
                'confidence_score' => 0.75,
                'source_processor' => 'enablement_momentum',
                'data' => [
                    'recent_attended' => $recentAttended,
                    'previous_attended' => $previousAttended,
                ],
                'expires_at' => now()->addDays(5),
            ]);
        }

        if ($previousAttended > 0 && $recentAttended > $previousAttended * 1.5) {
            $signals->push([
                'channel' => 'opportunity',
                'type' => 'enablement_attendance_surge',
                'severity' => 'positive',
                'title' => "Enablement attendance surge for {$partner->name}",
                'description' => sprintf(
                    '%d enablement sessions attended in the last 30 days vs %d previously — partner engagement is increasing.',
                    $recentAttended,
                    $previousAttended
                ),
                'recommended_action' => 'Capitalize on momentum — consider nominating for advanced programs or tier upgrade evaluation.',
                'confidence_score' => 0.75,
                'source_processor' => 'enablement_momentum',
                'data' => [
                    'recent_attended' => $recentAttended,
                    'previous_attended' => $previousAttended,
                ],
                'expires_at' => now()->addDays(7),
            ]);
        }
    }
}
