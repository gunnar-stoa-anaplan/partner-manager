<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\ModuleRegistry;
use Illuminate\View\View;

class FeaturePreviewController extends Controller
{
    public function __construct(
        private ModuleRegistry $registry,
    ) {}

    public function __invoke(Module $module): View
    {
        $user = auth()->user();
        $state = $this->registry->getFeatureState($module->slug, $user);
        $progress = $this->registry->getTrainingProgress($module->slug, $user);

        $activationPath = $module->activationPath()
            ->with(['steps' => fn ($q) => $q->orderBy('sort_order')])
            ->first();

        return view('features.preview', [
            'module' => $module,
            'state' => $state,
            'progress' => $progress,
            'activationPath' => $activationPath,
        ]);
    }
}
