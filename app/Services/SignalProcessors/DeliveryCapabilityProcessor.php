<?php

namespace App\Services\SignalProcessors;

use App\Contracts\SignalProcessor;
use App\Models\Partner;
use App\Services\DeliveryCapabilityRiskService;
use Illuminate\Support\Collection;

class DeliveryCapabilityProcessor implements SignalProcessor
{
    public function __construct(
        protected DeliveryCapabilityRiskService $deliveryService,
    ) {}

    public function name(): string
    {
        return 'delivery_capability';
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
            'description' => 'Identifies gaps between pipeline opportunities and certified or enablement-complete resources per product area. Flags when a partner has deals they may not be equipped to deliver.',
            'data_sources' => ['DeliveryCapabilityRiskService (per-product-area assessment)', 'Opportunity pipeline', 'Certification counts', 'Blueprint completion status'],
            'signal_types' => [
                'delivery_capability_gap' => [
                    'severity' => 'critical risk → critical, high → warning, warning → info',
                    'description' => 'Partner has pipeline in a product area with insufficient certified or trained resources.',
                ],
            ],
            'thresholds' => [
                'risk_levels' => 'critical, high, or warning (assessed by DeliveryCapabilityRiskService)',
                'confidence' => 'critical: 0.95, high: 0.85, warning: 0.70',
                'expiry' => '3 days',
            ],
        ];
    }

    public function process(Partner $partner): Collection
    {
        $signals = collect();

        $assessment = $this->deliveryService->assessPartner($partner);

        foreach ($assessment as $productArea) {
            $riskLevel = $productArea['risk_level'] ?? 'none';

            if (! in_array($riskLevel, ['critical', 'high', 'warning'])) {
                continue;
            }

            $severity = match ($riskLevel) {
                'critical' => 'critical',
                'high' => 'warning',
                default => 'info',
            };

            $area = $productArea['product_area'] ?? 'Unknown';
            $pipelineCount = $productArea['opportunity_count'] ?? 0;
            $certifiedResources = $productArea['certified_resources'] ?? 0;
            $completedResources = $productArea['completed_resources'] ?? 0;
            $pipelineAmount = $productArea['pipeline_amount'] ?? 0;

            $signals->push([
                'channel' => 'risk',
                'type' => 'delivery_capability_gap',
                'severity' => $severity,
                'title' => "Delivery capability gap in {$area} for {$partner->name}",
                'description' => sprintf(
                    '%s has %d forecasted %s in %s worth $%s, but only %d certified %s and %d enablement-complete %s.',
                    $partner->name,
                    $pipelineCount,
                    str('project')->plural($pipelineCount),
                    $area,
                    number_format($pipelineAmount),
                    $certifiedResources,
                    str('resource')->plural($certifiedResources),
                    $completedResources,
                    str('resource')->plural($completedResources)
                ),
                'recommended_action' => $this->buildRecommendation($productArea),
                'confidence_score' => $riskLevel === 'critical' ? 0.95 : ($riskLevel === 'high' ? 0.85 : 0.7),
                'source_processor' => 'delivery_capability',
                'data' => $productArea,
                'related_entity_ids' => [
                    'partner' => $partner->id,
                    'product_area' => $area,
                ],
                'expires_at' => now()->addDays(3),
            ]);
        }

        return $signals;
    }

    protected function buildRecommendation(array $productArea): string
    {
        $actions = [];

        if (($productArea['certified_resources'] ?? 0) < ($productArea['opportunity_count'] ?? 0)) {
            $actions[] = 'Prioritize certification for additional resources in this product area';
        }

        if (! ($productArea['blueprint_assigned'] ?? false)) {
            $actions[] = 'Assign the delivery enablement blueprint for this product area';
        }

        if ($productArea['next_session'] ?? null) {
            $actions[] = "Next enablement session: {$productArea['next_session']}";
        }

        return implode('. ', $actions) ?: 'Review resource allocation and enablement plan for this product area.';
    }
}
