<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivationPath;
use App\Services\ActivationService;
use App\Services\ModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Pennant\Feature;

class FeatureActivationController extends Controller
{
    /**
     * Feature Flags — admin panel for toggling modules on/off.
     */
    public function flags(ModuleRegistry $registry): View
    {
        $modules = $registry->allModules();

        $features = [];
        foreach ($modules as $slug => $definition) {
            $features[] = [
                'slug' => $slug,
                'name' => $definition['name'],
                'description' => $definition['description'],
                'icon' => $definition['icon'],
                'env_enabled' => config("modules.{$slug}.enabled", false),
                'pennant_active' => Feature::for(null)->active($slug),
                'effective' => $registry->isEnabled($slug),
            ];
        }

        return view('admin.activation.flags', compact('features'));
    }

    /**
     * Toggle a feature flag on or off via Pennant.
     */
    public function toggle(Request $request): RedirectResponse
    {
        $request->validate([
            'slug' => ['required', 'string'],
            'active' => ['required', 'boolean'],
        ]);

        if ($request->boolean('active')) {
            Feature::for(null)->activate($request->input('slug'));
        } else {
            Feature::for(null)->deactivate($request->input('slug'));
        }

        return back()->with('success', 'Feature flag updated.');
    }

    /**
     * Progression Dashboard — view all activation paths and their step progress.
     */
    public function progression(ActivationService $service): View
    {
        $paths = ActivationPath::with('steps.progress')
            ->where('is_active', true)
            ->get()
            ->map(function (ActivationPath $path) {
                $totalRequired = $path->steps->where('is_required', true)->count();
                $completedRequired = $path->steps
                    ->where('is_required', true)
                    ->filter(fn ($s) => $s->progress?->completed)
                    ->count();

                return [
                    'path' => $path,
                    'total_steps' => $path->steps->count(),
                    'total_required' => $totalRequired,
                    'completed_required' => $completedRequired,
                    'percent' => $totalRequired > 0 ? round(($completedRequired / $totalRequired) * 100) : 0,
                    'activated' => Feature::for(null)->active($path->feature_slug),
                ];
            });

        return view('admin.activation.progression', compact('paths'));
    }

    /**
     * Activation Log — recent Pennant feature events.
     */
    public function log(): View
    {
        $entries = \DB::table('features')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        return view('admin.activation.log', compact('entries'));
    }
}
