<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerHealthSnapshot;
use App\Services\PartnerHealthService;

class HealthDashboardController extends Controller
{
    public function index(PartnerHealthService $service): \Illuminate\View\View
    {
        $user = auth()->user();
        $partnersQuery = $user->isAdmin()
            ? Partner::query()
            : Partner::where('user_id', $user->id);

        $partners = $partnersQuery->select('id', 'name', 'geography', 'region')
            ->orderBy('name')
            ->get();

        $partnerIds = $partners->pluck('id');

        // Get latest snapshot per partner
        $snapshots = PartnerHealthSnapshot::whereIn('partner_id', $partnerIds)
            ->where('snapshot_date', '>=', now()->subDays(7))
            ->orderByDesc('snapshot_date')
            ->get()
            ->unique('partner_id')
            ->keyBy('partner_id');

        // Compute live for partners without recent snapshots
        $healthData = [];
        foreach ($partners as $partner) {
            if ($snapshots->has($partner->id)) {
                $snap = $snapshots[$partner->id];
                $healthData[$partner->id] = [
                    'score' => $snap->score,
                    'risk_level' => $snap->risk_level,
                    'signals' => $snap->signals ?? [],
                    'dimensions' => $snap->dimensions ?? [],
                    'snapshot_date' => $snap->snapshot_date,
                ];
            } else {
                $result = $service->computeForPartner($partner);
                $healthData[$partner->id] = array_merge($result, ['snapshot_date' => now()]);
            }
        }

        // Summary counts
        $summary = [
            'healthy' => collect($healthData)->where('risk_level', 'healthy')->count(),
            'watch' => collect($healthData)->where('risk_level', 'watch')->count(),
            'at_risk' => collect($healthData)->where('risk_level', 'at_risk')->count(),
            'critical' => collect($healthData)->where('risk_level', 'critical')->count(),
        ];

        return view('act.health.index', compact('partners', 'healthData', 'summary'));
    }

    public function show(Partner $partner, PartnerHealthService $service): \Illuminate\View\View
    {
        $current = $service->computeForPartner($partner);

        // Get 30-day trend
        $trend = PartnerHealthSnapshot::where('partner_id', $partner->id)
            ->where('snapshot_date', '>=', now()->subDays(30))
            ->orderBy('snapshot_date')
            ->get();

        return view('act.health.show', compact('partner', 'current', 'trend'));
    }
}
