<?php

namespace App\Services;

use App\Enums\StepType;
use App\Enums\TrainingPhase;
use App\Models\ActivationStep;
use App\Models\User;

class ActivationTracker
{
    public function __construct(
        private ActivationService $activationService,
    ) {}

    /**
     * Record that a user performed an action matching a Do-phase step.
     *
     * Finds all Do-phase steps that match the given feature slug and step type,
     * then increments progress by 1 for each.
     */
    public function track(User $user, string $featureSlug, StepType $stepType): void
    {
        $steps = ActivationStep::whereHas('activationPath', function ($q) use ($featureSlug, $user) {
            $q->where('feature_slug', $featureSlug)
                ->where('is_active', true)
                ->whereHas('users', fn ($u) => $u->where('user_id', $user->id));
        })
            ->where('phase', TrainingPhase::Do)
            ->where('step_type', $stepType)
            ->get();

        foreach ($steps as $step) {
            $progress = $step->progressFor($user)->first();
            $currentValue = $progress?->current_value ?? 0;

            $this->activationService->recordProgress($step, $currentValue + 1, $user);
        }
    }
}
