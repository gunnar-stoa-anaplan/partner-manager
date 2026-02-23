<?php

namespace App\Http\Controllers;

use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Services\ActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    /**
     * My Training dashboard — all paths assigned to the current user.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $paths = $user->activationPaths()
            ->with('steps.allProgress')
            ->get()
            ->map(function (ActivationPath $path) use ($user) {
                $totalRequired = $path->steps->where('is_required', true)->count();
                $completedRequired = $path->steps
                    ->where('is_required', true)
                    ->filter(fn ($s) => $s->progressFor($user)->first()?->completed)
                    ->count();

                $phasedSteps = $path->steps->groupBy(fn ($step) => $step->phase?->value ?? 'none');

                $phaseCompletion = [];
                foreach (['tell', 'show', 'do'] as $phaseValue) {
                    $phaseSteps = $phasedSteps->get($phaseValue, collect());
                    $phaseRequired = $phaseSteps->where('is_required', true);
                    $phaseCompleted = $phaseRequired->filter(
                        fn ($s) => $s->progressFor($user)->first()?->completed
                    )->count();

                    $phaseCompletion[$phaseValue] = [
                        'total' => $phaseRequired->count(),
                        'completed' => $phaseCompleted,
                        'all_complete' => $phaseRequired->count() > 0 && $phaseCompleted >= $phaseRequired->count(),
                    ];
                }

                return [
                    'path' => $path,
                    'total_required' => $totalRequired,
                    'completed_required' => $completedRequired,
                    'percent' => $totalRequired > 0 ? round(($completedRequired / $totalRequired) * 100) : 0,
                    'completed_at' => $path->pivot->completed_at,
                    'phased_steps' => $phasedSteps,
                    'phase_completion' => $phaseCompletion,
                    'has_phases' => $path->steps->whereNotNull('phase')->isNotEmpty(),
                ];
            });

        return view('training.index', compact('paths'));
    }

    /**
     * Single path detail with phases and step completion.
     */
    public function show(Request $request, ActivationPath $path): View
    {
        $user = $request->user();

        abort_unless(
            $user->activationPaths()->where('activation_path_id', $path->id)->exists(),
            403,
            'You are not assigned to this activation path.'
        );

        $path->load('steps.allProgress');

        $phasedSteps = $path->steps->groupBy(fn ($step) => $step->phase?->value ?? 'none');

        $phaseCompletion = [];
        foreach (['tell', 'show', 'do'] as $phaseValue) {
            $phaseSteps = $phasedSteps->get($phaseValue, collect());
            $phaseRequired = $phaseSteps->where('is_required', true);
            $phaseCompleted = $phaseRequired->filter(
                fn ($s) => $s->progressFor($user)->first()?->completed
            )->count();

            $phaseCompletion[$phaseValue] = [
                'total' => $phaseRequired->count(),
                'completed' => $phaseCompleted,
                'all_complete' => $phaseRequired->count() > 0 && $phaseCompleted >= $phaseRequired->count(),
            ];
        }

        $totalRequired = $path->steps->where('is_required', true)->count();
        $completedRequired = $path->steps
            ->where('is_required', true)
            ->filter(fn ($s) => $s->progressFor($user)->first()?->completed)
            ->count();
        $percent = $totalRequired > 0 ? round(($completedRequired / $totalRequired) * 100) : 0;

        return view('training.path', compact(
            'path', 'phasedSteps', 'phaseCompletion', 'user', 'percent', 'totalRequired', 'completedRequired'
        ));
    }

    /**
     * Step detail — primarily for Tell steps with rich content.
     */
    public function step(Request $request, ActivationStep $step): View
    {
        $user = $request->user();
        $path = $step->activationPath;

        abort_unless(
            $user->activationPaths()->where('activation_path_id', $path->id)->exists(),
            403,
            'You are not assigned to this activation path.'
        );

        $progress = $step->progressFor($user)->first();

        return view('training.step', compact('step', 'path', 'progress', 'user'));
    }

    /**
     * Mark a Tell/Show step as complete for the current user.
     */
    public function completeStep(Request $request, ActivationStep $step, ActivationService $service): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $path = $step->activationPath;

        abort_unless(
            $user->activationPaths()->where('activation_path_id', $path->id)->exists(),
            403
        );

        $targetValue = (float) ($step->criteria['target'] ?? 1);
        $service->recordProgress($step, $targetValue, $user);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('training.path', $path)->with('success', 'Step completed!');
    }

    /**
     * Return tour definition JSON for Shepherd.js.
     */
    public function tourData(Request $request, ActivationStep $step): JsonResponse
    {
        $user = $request->user();
        $path = $step->activationPath;

        abort_unless(
            $user->activationPaths()->where('activation_path_id', $path->id)->exists(),
            403
        );

        return response()->json([
            'step_id' => $step->id,
            'route' => $step->tour_route,
            'steps' => $step->tour_steps ?? [],
        ]);
    }
}
