<?php

namespace App\Http\Controllers;

use App\Models\AnaplanModel;
use App\Models\AnaplanVersionConfig;
use App\Models\AnaplanWorkspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnaplanOverviewController extends Controller
{
    public function index(): View
    {
        $versionConfigs = AnaplanVersionConfig::all()->keyBy('product_area');

        // Get all assigned workspaces with models, grouped by partner
        $workspaces = AnaplanWorkspace::whereNotNull('partner_id')
            ->with(['partner', 'models'])
            ->get();

        $productAreas = AnaplanWorkspace::PRODUCT_AREAS;
        $productAreaStats = [];

        foreach ($productAreas as $area) {
            $areaWorkspaces = $workspaces->where('product_area', $area);
            $areaModels = $areaWorkspaces->flatMap(fn ($ws) => $ws->models)->where('product_area', $area);
            $activeModels = $areaModels->where('active_state', '!=', 'ARCHIVED');

            // Version distribution: count partners on each version (use highest version per partner)
            $partnerVersions = $areaWorkspaces->groupBy('partner_id')->map(function ($partnerWorkspaces) use ($area) {
                $models = $partnerWorkspaces->flatMap(fn ($ws) => $ws->models)
                    ->where('product_area', $area)
                    ->where('active_state', '!=', 'ARCHIVED')
                    ->filter(fn ($m) => isset(AnaplanModel::VERSION_RANKS[$m->detected_version]) && AnaplanModel::VERSION_RANKS[$m->detected_version] > 0);

                if ($models->isEmpty()) {
                    return null;
                }

                $highestRank = $models->max(fn ($m) => AnaplanModel::VERSION_RANKS[$m->detected_version] ?? 0);
                $version = collect(AnaplanModel::VERSION_RANKS)
                    ->filter(fn ($rank) => $rank === $highestRank)
                    ->keys()
                    ->first();

                return [
                    'partner' => $partnerWorkspaces->first()->partner,
                    'latest_version' => $version,
                    'latest_rank' => $highestRank,
                    'model_count' => $models->count(),
                    'total_memory' => $models->sum('memory_usage'),
                    'last_synced' => $partnerWorkspaces->max('last_synced_at'),
                ];
            })->filter();

            // Count partners per version
            $versionDistribution = $partnerVersions->groupBy('latest_version')
                ->map(fn ($group) => $group->count())
                ->sortByDesc(function ($count, $version) {
                    return AnaplanModel::VERSION_RANKS[$version] ?? -1;
                });

            // Determine which partners are outdated
            $targetRank = isset($versionConfigs[$area])
                ? (AnaplanModel::VERSION_RANKS[$versionConfigs[$area]->current_version] ?? -1)
                : -1;

            $partnerList = $partnerVersions->map(function ($data) use ($targetRank) {
                $data['is_outdated'] = $targetRank > 0 && $data['latest_rank'] < $targetRank;

                return $data;
            })->sortByDesc('is_outdated');

            $productAreaStats[$area] = [
                'total_partners' => $partnerVersions->count(),
                'total_models' => $activeModels->count(),
                'total_memory' => $activeModels->sum('memory_usage'),
                'outdated_count' => $partnerList->where('is_outdated', true)->count(),
                'version_distribution' => $versionDistribution,
                'partners' => $partnerList,
            ];
        }

        $totalPartners = $workspaces->pluck('partner_id')->unique()->count();
        $totalModels = AnaplanModel::where('active_state', '!=', 'ARCHIVED')->count();
        $totalMemory = AnaplanModel::where('active_state', '!=', 'ARCHIVED')->sum('memory_usage');
        $lastSyncRaw = AnaplanWorkspace::max('last_synced_at');
        $lastSync = $lastSyncRaw ? \Carbon\Carbon::parse($lastSyncRaw) : null;

        return view('admin.anaplan-overview.index', compact(
            'versionConfigs', 'productAreas', 'productAreaStats',
            'totalPartners', 'totalModels', 'totalMemory', 'lastSync'
        ));
    }

    public function updateVersionConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_area' => ['required', 'string'],
            'current_version' => ['required', 'string', 'in:'.implode(',', AnaplanModel::VERSIONS)],
        ]);

        AnaplanVersionConfig::updateOrCreate(
            ['product_area' => $validated['product_area']],
            [
                'current_version' => $validated['current_version'],
                'updated_by' => auth()->id(),
            ]
        );

        return back()->with('success', "Current version for {$validated['product_area']} set to {$validated['current_version']}.");
    }
}
