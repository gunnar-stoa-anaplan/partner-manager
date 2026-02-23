<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StepType;
use App\Enums\TrainingPhase;
use App\Http\Controllers\Controller;
use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\User;
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
     * Progression Dashboard — per-user aggregated progress per path.
     */
    public function progression(): View
    {
        $paths = ActivationPath::with('steps.allProgress')
            ->withCount([
                'users as assigned_count',
                'users as completed_count' => fn ($q) => $q->whereNotNull('activation_path_user.completed_at'),
            ])
            ->where('is_active', true)
            ->get()
            ->map(function (ActivationPath $path) {
                $phasedSteps = $path->steps->groupBy(fn ($step) => $step->phase?->value ?? 'none');

                $phaseCompletion = [];
                foreach (['tell', 'show', 'do'] as $phaseValue) {
                    $phaseSteps = $phasedSteps->get($phaseValue, collect());
                    $phaseRequired = $phaseSteps->where('is_required', true);
                    $phaseCompletedUsers = $path->assigned_count > 0
                        ? $phaseRequired->min(fn ($s) => $s->allProgress->where('completed', true)->count())
                        : 0;

                    $phaseCompletion[$phaseValue] = [
                        'total' => $phaseRequired->count(),
                        'completed_users' => $phaseCompletedUsers ?? 0,
                        'all_complete' => $path->assigned_count > 0
                            && ($phaseCompletedUsers ?? 0) >= $path->assigned_count
                            && $phaseRequired->count() > 0,
                    ];
                }

                $totalRequired = $path->steps->where('is_required', true)->count();

                return [
                    'path' => $path,
                    'total_steps' => $path->steps->count(),
                    'total_required' => $totalRequired,
                    'completed_required' => $path->completed_count,
                    'assigned_count' => $path->assigned_count,
                    'completed_count' => $path->completed_count,
                    'percent' => $path->assigned_count > 0
                        ? round(($path->completed_count / $path->assigned_count) * 100)
                        : 0,
                    'activated' => Feature::for(null)->active($path->feature_slug),
                    'has_phases' => $path->steps->whereNotNull('phase')->isNotEmpty(),
                    'phased_steps' => $phasedSteps,
                    'phase_completion' => $phaseCompletion,
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
        $paths = ActivationPath::withCount(['steps', 'users as assigned_count'])
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
        $trainingPhases = TrainingPhase::cases();

        return view('admin.activation.paths', compact('paths', 'moduleSlugs', 'stepTypes', 'trainingPhases'));
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
     * Show a single activation path with its steps and user assignments.
     */
    public function showPath(ActivationPath $path): View
    {
        $path->load('steps.allProgress', 'users');

        $stepTypes = StepType::cases();
        $trainingPhases = TrainingPhase::cases();

        $pennantActive = Feature::for(null)->active($path->feature_slug);

        return view('admin.activation.path-show', compact('path', 'stepTypes', 'trainingPhases', 'pennantActive'));
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
            'content_body' => 'nullable|string|max:65000',
            'objectives' => 'nullable|array',
            'objectives.*' => 'string|max:500',
            'tour_route' => 'nullable|string|max:255',
            'tour_steps' => 'nullable|json',
            'step_type' => 'required|string|in:'.implode(',', array_column(StepType::cases(), 'value')),
            'target_value' => 'required|numeric|min:0',
            'is_required' => 'boolean',
            'phase' => 'nullable|string|in:'.implode(',', array_column(TrainingPhase::cases(), 'value')),
        ]);

        $maxOrder = $path->steps()->max('order') ?? 0;

        $path->steps()->create([
            'order' => $maxOrder + 1,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'content_body' => $validated['content_body'] ?? null,
            'objectives' => array_filter($validated['objectives'] ?? []),
            'tour_route' => $validated['tour_route'] ?? null,
            'tour_steps' => isset($validated['tour_steps']) ? json_decode($validated['tour_steps'], true) : null,
            'step_type' => $validated['step_type'],
            'criteria' => ['target' => (float) $validated['target_value']],
            'is_required' => $request->has('is_required'),
            'phase' => $validated['phase'] ?? null,
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
            'content_body' => 'nullable|string|max:65000',
            'objectives' => 'nullable|array',
            'objectives.*' => 'string|max:500',
            'tour_route' => 'nullable|string|max:255',
            'tour_steps' => 'nullable|json',
            'step_type' => 'required|string|in:'.implode(',', array_column(StepType::cases(), 'value')),
            'target_value' => 'required|numeric|min:0',
            'is_required' => 'boolean',
            'order' => 'required|integer|min:1',
            'phase' => 'nullable|string|in:'.implode(',', array_column(TrainingPhase::cases(), 'value')),
        ]);

        $step->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'content_body' => $validated['content_body'] ?? null,
            'objectives' => array_filter($validated['objectives'] ?? []),
            'tour_route' => $validated['tour_route'] ?? null,
            'tour_steps' => isset($validated['tour_steps']) ? json_decode($validated['tour_steps'], true) : null,
            'step_type' => $validated['step_type'],
            'criteria' => ['target' => (float) $validated['target_value']],
            'is_required' => $request->has('is_required'),
            'order' => $validated['order'],
            'phase' => $validated['phase'] ?? null,
        ]);

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
     * Manually mark a step as complete or reset it for a specific user.
     */
    public function toggleStepProgress(Request $request, ActivationStep $step, ActivationService $service): RedirectResponse
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
        ]);

        $user = $request->input('user_id')
            ? User::findOrFail($request->input('user_id'))
            : $request->user();

        $progress = $step->progressFor($user)->first();

        if ($progress && $progress->completed) {
            // Reset the step for this user
            $progress->update([
                'current_value' => 0,
                'completed' => false,
                'completed_at' => null,
            ]);

            // Clear the path completion if it was set
            $step->activationPath->users()->updateExistingPivot($user->id, [
                'completed_at' => null,
            ]);
        } else {
            // Complete the step for this user
            $targetValue = (float) ($step->criteria['target'] ?? 1);
            $service->recordProgress($step, $targetValue, $user);
        }

        return redirect()->route('admin.activation.paths.show', $step->activation_path_id)
            ->with('success', 'Step progress updated.');
    }

    /**
     * List users assigned to an activation path.
     */
    public function assignments(ActivationPath $path): View
    {
        $path->load('users');

        $availableUsers = User::whereDoesntHave('activationPaths', function ($q) use ($path) {
            $q->where('activation_path_id', $path->id);
        })->orderBy('name')->get();

        $roles = User::query()->distinct()->pluck('role')->sort()->values();

        return view('admin.activation.assignments', compact('path', 'availableUsers', 'roles'));
    }

    /**
     * Assign all users with a given role to an activation path.
     */
    public function assignRole(Request $request, ActivationPath $path, ActivationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|string',
        ]);

        $users = User::where('role', $validated['role'])
            ->whereDoesntHave('activationPaths', function ($q) use ($path) {
                $q->where('activation_path_id', $path->id);
            })
            ->get();

        if ($users->isEmpty()) {
            return redirect()->route('admin.activation.paths.assignments', $path)
                ->with('success', 'No new users with the '.ucfirst($validated['role']).' role to assign.');
        }

        $service->bulkAssignPath($path, $users, $request->user());

        return redirect()->route('admin.activation.paths.assignments', $path)
            ->with('success', $users->count().' '.ucfirst($validated['role']).' user(s) assigned.');
    }

    /**
     * Assign individual users to an activation path.
     */
    public function assignUsers(Request $request, ActivationPath $path, ActivationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();
        $service->bulkAssignPath($path, $users, $request->user());

        return redirect()->route('admin.activation.paths.assignments', $path)
            ->with('success', count($validated['user_ids']).' user(s) assigned.');
    }

    /**
     * Remove a user's assignment from an activation path.
     */
    public function unassignUser(ActivationPath $path, User $user): RedirectResponse
    {
        $path->users()->detach($user->id);

        // Also clean up their step progress for this path
        $stepIds = $path->steps()->pluck('id');
        $user->load([]);  // clear any cached relations
        \App\Models\StepProgress::where('user_id', $user->id)
            ->whereIn('activation_step_id', $stepIds)
            ->delete();

        return redirect()->route('admin.activation.paths.assignments', $path)
            ->with('success', "{$user->name} has been unassigned.");
    }

    /**
     * Show a single user's progress across all assigned paths.
     */
    public function userProgress(User $user): View
    {
        $paths = $user->activationPaths()
            ->with('steps')
            ->get()
            ->map(function (ActivationPath $path) use ($user) {
                $totalRequired = $path->steps->where('is_required', true)->count();
                $completedRequired = $path->steps
                    ->where('is_required', true)
                    ->filter(fn ($s) => $s->progressFor($user)->first()?->completed)
                    ->count();

                return [
                    'path' => $path,
                    'total_required' => $totalRequired,
                    'completed_required' => $completedRequired,
                    'percent' => $totalRequired > 0 ? round(($completedRequired / $totalRequired) * 100) : 0,
                    'completed_at' => $path->pivot->completed_at,
                ];
            });

        return view('admin.activation.user-progress', compact('user', 'paths'));
    }
}
