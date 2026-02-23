<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\Partner;
use App\Models\PartnerAlert;
use App\Services\AlertGenerationService;
use Illuminate\Support\Collection;

class AlertBridgeProcessor implements SignalProcessor
{
    public function __construct(
        protected AlertGenerationService $alertService,
    ) {}

    public function name(): string
    {
        return 'alert_bridge';
    }

    public function channel(): string
    {
        return 'risk';
    }

    public function cadence(): string
    {
        return 'daily';
    }

    public function describe(): array
    {
        return [
            'description' => 'Converts active partner alerts into signal engine signals. Maps alert severity to signal severity and links the original alert for traceability.',
            'data_sources' => ['PartnerAlert (active, not already linked to a signal)'],
            'signal_types' => [
                'alert_{type}' => [
                    'severity' => 'critical/high → critical, medium → warning, low → info',
                    'description' => 'Mirrors the source alert with a 7-day expiration window.',
                ],
            ],
            'thresholds' => [
                'confidence' => 'critical: 0.95, high: 0.85, medium/low: 0.70',
                'expiry' => '7 days',
                'dedup' => 'Skips alerts already linked to an active/acknowledged signal',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $alerts = PartnerAlert::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->whereNotIn('id', function ($query) use ($partner) {
                $query->select('linked_alert_id')
                    ->from('signals')
                    ->where('partner_id', $partner->id)
                    ->whereNotNull('linked_alert_id')
                    ->whereIn('status', ['active', 'acknowledged']);
            })
            ->get();

        return $alerts->map(function (PartnerAlert $alert) {
            $severityMap = [
                'critical' => 'critical',
                'high' => 'critical',
                'medium' => 'warning',
                'low' => 'info',
            ];

            return [
                'channel' => 'risk',
                'type' => 'alert_'.$alert->type,
                'severity' => $severityMap[$alert->severity] ?? 'info',
                'title' => $alert->title,
                'description' => $alert->description,
                'recommended_action' => $alert->recommended_action,
                'confidence_score' => $alert->severity === 'critical' ? 0.95 : ($alert->severity === 'high' ? 0.85 : 0.7),
                'source_processor' => 'alert_bridge',
                'data' => [
                    'alert_type' => $alert->type,
                    'alert_severity' => $alert->severity,
                    'original_alert_id' => $alert->id,
                ],
                'related_entity_ids' => ['partner_alert' => $alert->id],
                'linked_alert_id' => $alert->id,
                'expires_at' => now()->addDays(7),
            ];
        });
    }
}
