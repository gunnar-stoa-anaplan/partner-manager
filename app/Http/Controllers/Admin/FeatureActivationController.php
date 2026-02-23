<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StepType;
use App\Http\Controllers\Controller;
use App\Models\ActivationPath;
use App\Models\ActivationStep;
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
    public function progression(): View
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

    /**
     * Activation Paths — list all paths with a create form.
     */
    public function paths(ModuleRegistry $registry): View
    {
        $paths = ActivationPath::withCount('steps')
            ->orderBy('name')
            ->get()
            ->map(function (ActivationPath $path) {
                $path->pennant_active = Feature::for(null)->active($path->feature_slug);

                return $path;
            });

        $moduleSlugs = collect($registry->allModules())
            ->map(fn (array $def, string $slug) => ['slug' => $slug, 'name' => $def['name']])
            ->values()
            ->toArray();

        $stepTypes = StepType::cases();

        return view('admin.activation.paths', compact('paths', 'moduleSlugs', 'stepTypes'));
    }

    /**
     * Store a new activation path.
     */
    public function storePath(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'feature_slug' => 'required|string|max:100|unique:activation_paths,feature_slug',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        ActivationPath::create($validated);

        return redirect()->route('admin.activation.paths')->with('success', 'Activation path created.');
    }

    /**
     * Show a single activation path with its steps for editing.
     */
    public function showPath(ActivationPath $path): View
    {
        $path->load('steps.progress');

        $stepTypes = StepType::cases();

        $pennantActive = Feature::for(null)->active($path->feature_slug);

        return view('admin.activation.path-show', compact('path', 'stepTypes', 'pennantActive'));
    }

    /**
     * Update an activation path.
     */
    public function updatePath(Request $request, ActivationPath $path): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $path->update($validated);

        return redirect()->route('admin.activation.paths.show', $path)->with('success', 'Activation path updated.');
    }

    /**
     * Delete an activation path and its steps.
     */
    public function destroyPath(ActivationPath $path): RedirectResponse
    {
        $path->delete();

        return redirect()->route('admin.activation.paths')->with('success', 'Activation path deleted.');
    }

    /**
     * Add a step to an activation path.
     */
    public function storeStep(Request $request, ActivationPath $path): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'step_type' => 'required|string|in:'.implode(',', array_column(StepType::cases(), 'value')),
            'target_value' => 'required|numeric|min:0',
            'is_required' => 'boolean',
        ]);

        $maxOrder = $path->steps()->max('order') ?? 0;

        $path->steps()->create([
            'order' => $maxOrder + 1,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'step_type' => $validated['step_type'],
            'criteria' => ['target' => (float) $validated['target_value']],
            'is_required' => $request->has('is_required'),
        ]);

        return redirect()->route('admin.activation.paths.show', $path)->with('success', 'Step added.');
    }

    /**
     * Update an activation step.
     */
    public function updateStep(Request $request, ActivationStep $step): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'step_type' => 'required|string|in:'.implode(',', array_column(StepType::cases(), 'value')),
            'target_value' => 'required|numeric|min:0',
            'is_required' => 'boolean',
            'order' => 'required|integer|min:1',
        ]);

        $step->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'step_type' => $validated['step_type'],
            'criteria' => ['target' => (float) $validated['target_value']],
            'is_required' => $request->has('is_required'),
            'order' => $validated['order'],
        ]);

        // Update target_value on existing progress if it exists
        if ($step->progress) {
            $step->progress->update(['target_value' => (float) $validated['target_value']]);
        }

        return redirect()->route('admin.activation.paths.show', $step->activation_path_id)->with('success', 'Step updated.');
    }

    /**
     * Delete an activation step.
     */
    public function destroyStep(ActivationStep $step): RedirectResponse
    {
        $pathId = $step->activation_path_id;

        $step->delete();

        return redirect()->route('admin.activation.paths.show', $pathId)->with('success', 'Step deleted.');
    }

    /**
     * Manually mark a step as complete or reset it.
     */
    public function toggleStepProgress(Request $request, ActivationStep $step, ActivationService $service): RedirectResponse
    {
        $progress = $step->progress;

        if ($progress && $progress->completed) {
            // Reset the step
            $progress->update([
                'current_value' => 0,
                'completed' => false,
                'completed_at' => null,
            ]);
        } else {
            // Complete the step
            $targetValue = (float) ($step->criteria['target'] ?? 1);
            $service->recordProgress($step, $targetValue);
        }

        return redirect()->route('admin.activation.paths.show', $step->activation_path_id)
            ->with('success', 'Step progress updated.');
    }
}
