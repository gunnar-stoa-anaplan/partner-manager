<?php

namespace App\Services;

use App\Models\ActivationPath;
use App\Models\ActivationStep;
use Laravel\Pennant\Feature;

class ActivationService
{
    /**
     * Evaluate whether all required steps for a path are complete.
     * If so, activate the corresponding Pennant feature.
     */
    public function evaluate(ActivationPath $path): bool
    {
        $requiredSteps = $path->steps()
            ->where('is_required', true)
            ->with('progress')
            ->get();

        foreach ($requiredSteps as $step) {
            if (! $step->progress?->completed) {
                return false;
            }
        }

        Feature::for(null)->activate($path->feature_slug);

        return true;
    }

    /**
     * Record progress for a given step and re-evaluate the path.
     */
    public function recordProgress(ActivationStep $step, float $value): void
    {
        $progress = $step->progress ?? $step->progress()->create([
            'target_value' => $step->criteria['target'] ?? 1,
        ]);

        $progress->current_value = $value;

        if ($value >= $progress->target_value && ! $progress->completed) {
            $progress->completed = true;
            $progress->completed_at = now();
        }

        $progress->save();

        $this->evaluate($step->activationPath);
    }

    /**
     * Build a telemetry snapshot of all activation paths and their progress.
     *
     * @return array<int, array{feature: string, activated: bool, steps: array<int, array<string, mixed>>}>
     */
    public function getSnapshot(): array
    {
        return ActivationPath::with('steps.progress')
            ->where('is_active', true)
            ->get()
            ->map(fn (ActivationPath $path) => [
                'feature' => $path->feature_slug,
                'activated' => Feature::for(null)->active($path->feature_slug),
                'steps' => $path->steps->map(fn (ActivationStep $step) => [
                    'name' => $step->name,
                    'type' => $step->step_type->value,
                    'required' => $step->is_required,
                    'current' => (float) ($step->progress?->current_value ?? 0),
                    'target' => (float) ($step->progress?->target_value ?? 1),
                    'completed' => (bool) $step->progress?->completed,
                ])->toArray(),
            ])
            ->toArray();
    }
}
