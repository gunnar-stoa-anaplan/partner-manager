<?php

namespace App\Services;

use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\User;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

class ActivationService
{
    /**
     * Evaluate whether all required steps for a path are complete for a given user.
     * If so, mark the pivot completed_at. Does NOT touch Pennant (instance-level).
     */
    public function evaluate(ActivationPath $path, User $user): bool
    {
        $requiredSteps = $path->steps()
            ->where('is_required', true)
            ->get();

        foreach ($requiredSteps as $step) {
            $progress = $step->progressFor($user)->first();

            if (! $progress?->completed) {
                return false;
            }
        }

        // All required steps complete — mark pivot
        $path->users()->syncWithoutDetaching([
            $user->id => ['completed_at' => now()],
        ]);

        // Flash unlock celebration for the next page load
        if (session()->isStarted()) {
            session()->flash('feature_unlocked', $path->feature_slug);
        }

        return true;
    }

    /**
     * Record progress for a given step scoped to a user, then re-evaluate.
     */
    public function recordProgress(ActivationStep $step, float $value, User $user): void
    {
        $progress = $step->progressFor($user)->first()
            ?? $step->allProgress()->create([
                'user_id' => $user->id,
                'target_value' => $step->criteria['target'] ?? 1,
            ]);

        $progress->current_value = $value;

        if ($value >= $progress->target_value && ! $progress->completed) {
            $progress->completed = true;
            $progress->completed_at = now();
        }

        $progress->save();

        $this->evaluate($step->activationPath, $user);
    }

    /**
     * Assign an activation path to a user.
     */
    public function assignPath(ActivationPath $path, User $user, ?User $assignedBy = null): void
    {
        $path->users()->syncWithoutDetaching([
            $user->id => [
                'assigned_by' => $assignedBy?->id,
                'assigned_at' => now(),
            ],
        ]);
    }

    /**
     * Bulk-assign an activation path to multiple users.
     */
    public function bulkAssignPath(ActivationPath $path, Collection $users, ?User $assignedBy = null): void
    {
        $pivotData = $users->mapWithKeys(fn (User $u) => [
            $u->id => [
                'assigned_by' => $assignedBy?->id,
                'assigned_at' => now(),
            ],
        ])->all();

        $path->users()->syncWithoutDetaching($pivotData);
    }

    /**
     * Build a telemetry snapshot of all activation paths with per-user aggregates.
     *
     * @return array<int, array{feature: string, instance_active: bool, assigned_count: int, completed_count: int, steps: array<int, array<string, mixed>>}>
     */
    public function getSnapshot(): array
    {
        return ActivationPath::with('steps.allProgress')
            ->withCount([
                'users as assigned_count',
                'users as completed_count' => fn ($q) => $q->whereNotNull('activation_path_user.completed_at'),
            ])
            ->where('is_active', true)
            ->get()
            ->map(fn (ActivationPath $path) => [
                'feature' => $path->feature_slug,
                'instance_active' => Feature::for(null)->active($path->feature_slug),
                'assigned_count' => $path->assigned_count,
                'completed_count' => $path->completed_count,
                'steps' => $path->steps->map(fn (ActivationStep $step) => [
                    'name' => $step->name,
                    'type' => $step->step_type->value,
                    'phase' => $step->phase?->value,
                    'required' => $step->is_required,
                    'completed_count' => $step->allProgress->where('completed', true)->count(),
                    'assigned_count' => $path->assigned_count,
                ])->toArray(),
            ])
            ->toArray();
    }
}
