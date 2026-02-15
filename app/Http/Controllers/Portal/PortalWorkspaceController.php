<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AnaplanModel;
use App\Models\AnaplanVersionConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalWorkspaceController extends Controller
{
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $workspaces = $partner->anaplanWorkspaces()
            ->with(['models'])
            ->orderBy('name')
            ->get();

        $allModels = $workspaces->flatMap(fn ($ws) => $ws->models);
        $activeModels = $allModels->where('active_state', '!=', 'ARCHIVED');
        $totalMemory = $activeModels->sum('memory_usage');
        $versionConfigs = AnaplanVersionConfig::all()->keyBy('product_area');

        // Check for outdated versions
        $outdatedAreas = [];
        foreach ($versionConfigs as $area => $config) {
            $areaModels = $activeModels->where('product_area', $area)
                ->filter(fn ($m) => isset(AnaplanModel::VERSION_RANKS[$m->detected_version]) && AnaplanModel::VERSION_RANKS[$m->detected_version] > 0);

            if ($areaModels->isEmpty()) {
                continue;
            }

            $targetRank = AnaplanModel::VERSION_RANKS[$config->current_version] ?? -1;
            $highestRank = $areaModels->max(fn ($m) => AnaplanModel::VERSION_RANKS[$m->detected_version] ?? 0);
            $highestVersion = collect(AnaplanModel::VERSION_RANKS)
                ->filter(fn ($r) => $r === $highestRank)
                ->keys()
                ->first();

            if ($highestRank < $targetRank) {
                $outdatedAreas[] = [
                    'area' => $area,
                    'current' => $highestVersion,
                    'target' => $config->current_version,
                ];
            }
        }

        return view('portal.workspaces.index', [
            'partner' => $partner,
            'workspaces' => $workspaces,
            'activeModelCount' => $activeModels->count(),
            'totalMemory' => $totalMemory,
            'outdatedAreas' => $outdatedAreas,
        ]);
    }
}
