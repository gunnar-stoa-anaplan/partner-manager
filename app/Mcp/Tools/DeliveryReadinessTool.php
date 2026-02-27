<?php

namespace App\Mcp\Tools;

use App\Models\Certification;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Services\DeliveryCapabilityRiskService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Assess delivery readiness across the partner portfolio by product area. Shows forecasted projects, available certified resources, enablement completion, and risk assessment per product area. Answers questions like "Do I have the right partners to deliver all forecasted FY27 projects?"')]
class DeliveryReadinessTool extends Tool
{
    public function handle(Request $request): Response
    {
        $productAreaFilter = $request->get('product_area');
        $partnerId = $request->get('partner_id');

        if ($partnerId) {
            return $this->assessSinglePartner($partnerId, $productAreaFilter);
        }

        return $this->assessPortfolio($productAreaFilter);
    }

    protected function assessSinglePartner(int $partnerId, ?string $productAreaFilter): Response
    {
        $partner = Partner::findOrFail($partnerId);
        $service = app(DeliveryCapabilityRiskService::class);
        $assessment = $service->assessPartner($partner);

        if ($productAreaFilter) {
            $assessment = $assessment->filter(
                fn ($a) => stripos($a['product_area'] ?? '', $productAreaFilter) !== false
            );
        }

        return Response::json([
            'partner' => $partner->name,
            'product_areas' => $assessment->values()->all(),
            'summary' => [
                'total_areas' => $assessment->count(),
                'critical_gaps' => $assessment->where('risk_level', 'critical')->count(),
                'high_risk' => $assessment->where('risk_level', 'high')->count(),
                'total_pipeline' => $assessment->sum('pipeline_amount'),
                'total_certified_resources' => $assessment->sum('certified_resources'),
            ],
        ]);
    }

    protected function assessPortfolio(?string $productAreaFilter): Response
    {
        $service = app(DeliveryCapabilityRiskService::class);

        $portfolioByArea = [];

        $openOpps = Opportunity::query()
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->whereNotNull('product_area')
            ->when($productAreaFilter, fn ($q) => $q->where('product_area', 'like', "%{$productAreaFilter}%"))
            ->selectRaw('product_area, COUNT(*) as opp_count, SUM(amount) as total_amount')
            ->groupBy('product_area')
            ->get();

        foreach ($openOpps as $opp) {
            $area = $opp->product_area;

            $certifiedCount = Certification::where('product_area', $area)
                ->where('status', 'active')
                ->distinct('contact_id')
                ->count('contact_id');

            $partnerCount = Opportunity::where('product_area', $area)
                ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
                ->distinct('partner_id')
                ->count('partner_id');

            $gap = max(0, $opp->opp_count - $certifiedCount);

            $portfolioByArea[] = [
                'product_area' => $area,
                'forecasted_projects' => $opp->opp_count,
                'pipeline_value' => (float) $opp->total_amount,
                'certified_resources' => $certifiedCount,
                'partners_with_pipeline' => $partnerCount,
                'resource_gap' => $gap,
                'coverage_ratio' => $opp->opp_count > 0 ? round($certifiedCount / $opp->opp_count, 2) : 0,
                'risk_level' => $this->classifyRisk($certifiedCount, $opp->opp_count),
            ];
        }

        usort($portfolioByArea, fn ($a, $b) => $b['resource_gap'] <=> $a['resource_gap']);

        $totalForecasted = array_sum(array_column($portfolioByArea, 'forecasted_projects'));
        $totalCertified = array_sum(array_column($portfolioByArea, 'certified_resources'));
        $criticalAreas = count(array_filter($portfolioByArea, fn ($a) => $a['risk_level'] === 'critical'));

        return Response::json([
            'portfolio_summary' => [
                'total_product_areas' => count($portfolioByArea),
                'total_forecasted_projects' => $totalForecasted,
                'total_certified_resources' => $totalCertified,
                'overall_coverage_ratio' => $totalForecasted > 0 ? round($totalCertified / $totalForecasted, 2) : 0,
                'critical_areas' => $criticalAreas,
                'high_risk_areas' => count(array_filter($portfolioByArea, fn ($a) => $a['risk_level'] === 'high')),
            ],
            'product_areas' => $portfolioByArea,
        ]);
    }

    protected function classifyRisk(int $certified, int $forecasted): string
    {
        if ($forecasted === 0) {
            return 'none';
        }

        $ratio = $certified / $forecasted;

        return match (true) {
            $ratio < 0.25 => 'critical',
            $ratio < 0.5 => 'high',
            $ratio < 0.75 => 'warning',
            $ratio < 1.0 => 'low',
            default => 'none',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'partner_id' => $schema->integer()->description('Optional: assess a specific partner. If omitted, returns portfolio-wide assessment.'),
            'product_area' => $schema->string()->description('Optional: filter by product area name (partial match)'),
        ];
    }
}
