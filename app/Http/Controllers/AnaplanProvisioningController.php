<?php

namespace App\Http\Controllers;

use App\Models\AnaplanModel;
use App\Models\AnaplanWorkspace;
use App\Models\Partner;
use App\Services\AnaplanApiService;
use Illuminate\Http\Request;

class AnaplanProvisioningController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $workspaces = AnaplanWorkspace::with(['partner', 'models'])
            ->orderBy('name')
            ->get();

        $partners = Partner::orderBy('name')->get();
        $unassignedCount = $workspaces->whereNull('partner_id')->count();
        $totalModels = AnaplanModel::count();
        $activeModels = AnaplanModel::where('active_state', '!=', 'ARCHIVED')->count();
        $lastSync = $workspaces->max('last_synced_at');
        $isConfigured = app(AnaplanApiService::class)->isConfigured();

        return view('admin.anaplan-provisioning.index', compact(
            'workspaces', 'partners', 'unassignedCount',
            'totalModels', 'activeModels', 'lastSync', 'isConfigured'
        ));
    }

    public function assignPartner(Request $request, AnaplanWorkspace $workspace): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'partner_id' => ['nullable', 'exists:partners,id'],
        ]);

        $workspace->update(['partner_id' => $validated['partner_id']]);

        $partnerName = $validated['partner_id']
            ? Partner::find($validated['partner_id'])->name
            : 'None';

        return back()->with('success', "Workspace \"{$workspace->name}\" assigned to {$partnerName}.");
    }

    public function sync(): \Illuminate\Http\RedirectResponse
    {
        $service = app(AnaplanApiService::class);

        if (! $service->isConfigured()) {
            return back()->with('error', 'Anaplan API credentials are not configured. Set ANAPLAN_USERNAME and ANAPLAN_PASSWORD in .env.');
        }

        $result = $service->syncAll();

        if (! empty($result['errors'])) {
            return back()->with('warning', 'Sync completed with errors: '.implode('; ', $result['errors'])
                ." ({$result['workspaces_synced']} workspaces, {$result['models_synced']} models synced).");
        }

        return back()->with('success', "Sync complete: {$result['workspaces_synced']} workspaces and {$result['models_synced']} models synced.");
    }
}
