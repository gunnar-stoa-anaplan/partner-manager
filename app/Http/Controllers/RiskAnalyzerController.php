<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Services\RiskAnalyzerService;
use Illuminate\Http\Request;

class RiskAnalyzerController extends Controller
{
    private const TABS = [
        'portfolio' => 'Portfolio Dashboard',
        'geography' => 'Geography',
        'lob' => 'LOB Breakdown',
        'trends' => 'Historical Trends',
        'deltas' => 'Week-over-Week',
        'opp-movement' => 'Opp Movement',
        'resource-movement' => 'Resource Movement',
        'risk-scoring' => 'Risk Scoring',
        'heatmap' => 'Resource Heatmap',
        'stage-aging' => 'Stage Aging',
        'recommendations' => 'Recommendations',
        'partner-cards' => 'Partner Cards',
        'coverage' => 'Resource Coverage',
    ];

    public function __construct(
        private RiskAnalyzerService $service,
    ) {}

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'portfolio');
        $user = auth()->user();

        $partnerIds = $user->isAdmin()
            ? null
            : Partner::where('user_id', $user->id)->pluck('id');

        $snapshotDates = $this->service->snapshotDates();
        $snapshotDate = $request->get('snapshot', $snapshotDates->last());

        $data = match ($tab) {
            'portfolio' => $this->portfolio($snapshotDate, $partnerIds),
            'geography' => $this->geography($snapshotDate, $partnerIds),
            'lob' => $this->lob($snapshotDate, $partnerIds),
            'trends' => $this->trends($partnerIds),
            'deltas' => $this->deltas($partnerIds),
            'opp-movement' => $this->oppMovement($snapshotDates, $request, $partnerIds),
            'resource-movement' => $this->resourceMovement($snapshotDates, $request, $partnerIds),
            'risk-scoring' => $this->riskScoring($snapshotDate, $partnerIds),
            'heatmap' => $this->heatmap($partnerIds, $snapshotDate),
            'stage-aging' => $this->stageAging($snapshotDate, $partnerIds),
            'recommendations' => $this->recommendations($snapshotDate, $partnerIds),
            'partner-cards' => $this->partnerCards($snapshotDate, $partnerIds),
            'coverage' => $this->coverage($partnerIds, $snapshotDate),
            default => $this->portfolio($snapshotDate, $partnerIds),
        };

        $data['tab'] = $tab;
        $data['tabs'] = self::TABS;
        $data['snapshotDates'] = $snapshotDates;
        $data['currentSnapshot'] = $snapshotDate;

        return view('risk-analyzer.index', $data);
    }

    private function portfolio(string $snapshotDate, $partnerIds): array
    {
        return ['summary' => $this->service->portfolioSummary($snapshotDate, $partnerIds)];
    }

    private function geography(string $snapshotDate, $partnerIds): array
    {
        return [
            'geoBreakdown' => $this->service->dimensionBreakdown($snapshotDate, 'geo', $partnerIds),
            'regionBreakdown' => $this->service->dimensionBreakdown($snapshotDate, 'region', $partnerIds),
        ];
    }

    private function lob(string $snapshotDate, $partnerIds): array
    {
        return [
            'lobBreakdown' => $this->service->dimensionBreakdown($snapshotDate, 'lob', $partnerIds),
            'stageBreakdown' => $this->service->dimensionBreakdown($snapshotDate, 'stage', $partnerIds),
        ];
    }

    private function trends($partnerIds): array
    {
        return ['trends' => $this->service->historicalTrends($partnerIds)];
    }

    private function deltas($partnerIds): array
    {
        return $this->service->weekOverWeekDeltas($partnerIds);
    }

    private function oppMovement($snapshotDates, Request $request, $partnerIds): array
    {
        if ($snapshotDates->count() < 2) {
            return ['movement' => null, 'fromDate' => null, 'toDate' => null];
        }

        $fromDate = $request->get('from', $snapshotDates[$snapshotDates->count() - 2]);
        $toDate = $request->get('to', $snapshotDates->last());

        return [
            'movement' => $this->service->opportunityMovement($fromDate, $toDate, $partnerIds),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    private function resourceMovement($snapshotDates, Request $request, $partnerIds): array
    {
        if ($snapshotDates->count() < 2) {
            return ['movement' => null, 'fromDate' => null, 'toDate' => null];
        }

        $fromDate = $request->get('from', $snapshotDates[$snapshotDates->count() - 2]);
        $toDate = $request->get('to', $snapshotDates->last());

        return [
            'movement' => $this->service->resourceMovement($fromDate, $toDate, $partnerIds),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    private function riskScoring(string $snapshotDate, $partnerIds): array
    {
        $scored = $this->service->deliveryRiskScoring($snapshotDate, $partnerIds);

        return [
            'scoredOpps' => $scored,
            'riskSummary' => [
                'very_high' => $scored->where('risk_level', 'Very High Risk')->count(),
                'high' => $scored->where('risk_level', 'High Risk')->count(),
                'low' => $scored->where('risk_level', 'Low Risk')->count(),
                'very_high_nnacv' => $scored->where('risk_level', 'Very High Risk')->sum('amount'),
                'high_nnacv' => $scored->where('risk_level', 'High Risk')->sum('amount'),
                'low_nnacv' => $scored->where('risk_level', 'Low Risk')->sum('amount'),
            ],
        ];
    }

    private function heatmap($partnerIds, ?string $snapshotDate = null): array
    {
        return ['heatmap' => $this->service->resourceHeatmap($partnerIds, $snapshotDate)];
    }

    private function stageAging(string $snapshotDate, $partnerIds): array
    {
        return ['aging' => $this->service->stageAging($snapshotDate, $partnerIds)];
    }

    private function recommendations(string $snapshotDate, $partnerIds): array
    {
        return ['recommendations' => $this->service->recommendations($snapshotDate, $partnerIds)];
    }

    private function partnerCards(string $snapshotDate, $partnerIds): array
    {
        return ['cards' => $this->service->partnerRiskCards($snapshotDate, $partnerIds)];
    }

    private function coverage($partnerIds, ?string $snapshotDate = null): array
    {
        return ['heatmap' => $this->service->resourceHeatmap($partnerIds, $snapshotDate)];
    }
}
