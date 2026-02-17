<?php

namespace App\Http\Controllers;

use App\Services\SystemStatusService;
use App\Services\VersionCheckService;
use Illuminate\View\View;

class SystemStatusController extends Controller
{
    public function __construct(
        private SystemStatusService $systemStatus,
        private VersionCheckService $versionCheck
    ) {}

    public function index(): View
    {
        return view('admin.system-status', [
            'currentVersion' => $this->versionCheck->currentVersion(),
            'availableUpdate' => $this->versionCheck->getAvailableUpdate(),
            'isUpdateAccepted' => $this->versionCheck->isUpdateAccepted(),
            'database' => $this->systemStatus->checkDatabase(),
            'cache' => $this->systemStatus->checkCache(),
            'queue' => $this->systemStatus->checkQueue(),
            'mail' => $this->systemStatus->checkMail(),
            'agent' => $this->systemStatus->getAgentStatus(),
            'runtime' => $this->systemStatus->getRuntimeInfo(),
            'dbMetrics' => $this->systemStatus->getDatabaseMetrics(),
        ]);
    }
}
