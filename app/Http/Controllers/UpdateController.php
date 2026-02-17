<?php

namespace App\Http\Controllers;

use App\Services\VersionCheckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpdateController extends Controller
{
    public function __construct(private VersionCheckService $versionCheckService) {}

    /**
     * Show available update details.
     */
    public function show(): View|RedirectResponse
    {
        $update = $this->versionCheckService->getAvailableUpdate();

        if ($update === null) {
            return redirect()->route('admin.dashboard')
                ->with('info', 'Your application is up to date.');
        }

        return view('admin.update', [
            'currentVersion' => $this->versionCheckService->currentVersion(),
            'update' => $update,
            'isAccepted' => $this->versionCheckService->isUpdateAccepted(),
        ]);
    }

    /**
     * Accept the available update.
     */
    public function accept(Request $request): RedirectResponse
    {
        $update = $this->versionCheckService->getAvailableUpdate();

        if ($update === null) {
            return redirect()->route('admin.dashboard')
                ->with('info', 'No update available to accept.');
        }

        if ($this->versionCheckService->isUpdateAccepted()) {
            return redirect()->route('admin.update')
                ->with('info', 'This update has already been accepted and is pending deployment.');
        }

        $accepted = $this->versionCheckService->acceptUpdate(
            $update['version'],
            $request->user()->id
        );

        if (! $accepted) {
            return redirect()->route('admin.update')
                ->with('error', 'Unable to accept update. The agent status path may not be available.');
        }

        return redirect()->route('admin.update')
            ->with('success', 'Update accepted. The new version will be deployed within the next 60 seconds.');
    }
}
