<?php

namespace App\Services;

use App\Models\ActivationPath;
use App\Models\ActivationStep;
use App\Models\StepProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

class ActivationAggregateService
{
    /**
     * Build anonymized activation aggregate for Tier 2 control plane.
     * Contains timing, velocity, and bottleneck data per path/step/phase.
     *
     * @return array{paths: array, summary: array, collected_at: string}
     */
    public function buildAggregate(): array
    {
        $paths = $this->buildPathMetrics();

        return [
            'paths' => $paths,
            'summary' => $this->buildSummary($paths),
            'collected_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildPathMetrics(): array
    {
        return ActivationPath::with(['steps.allProgress', 'users'])
            ->withCount([
                'users as assigned_count',
                'users as completed_count' => fn ($q) => $q->whereNotNull('activation_path_user.completed_at'),
            ])
            ->where('is_active', true)
            ->get()
            ->map(function (ActivationPath $path) {
                $assignedCount = $path->assigned_count;
                $completedCount = $path->completed_count;

                $completionRate = $assignedCount > 0
                    ? round($completedCount / $assignedCount, 4)
                    : 0;

                $velocityDays = $this->computePathVelocity($path);
                $phases = $this->computePhaseMetrics($path);
                $velocity = $this->computeRecentVelocity($path);

                return [
                    'feature' => $path->feature_slug,
                    'instance_active' => Feature::for(null)->active($path->feature_slug),
                    'assigned_count' => $assignedCount,
                    'completed_count' => $completedCount,
                    'completion_rate' => $completionRate,
                    'avg_days_to_complete' => $velocityDays['avg'],
                    'median_days_to_complete' => $velocityDays['median'],
                    'phases' => $phases,
                    'steps' => $path->steps->map(function (ActivationStep $step) use ($assignedCount) {
                        $stepCompleted = $step->allProgress->where('completed', true)->count();

                        return [
                            'name' => $step->name,
                            'phase' => $step->phase?->value,
                            'required' => $step->is_required,
                            'completion_rate' => $assignedCount > 0
                                ? round($stepCompleted / $assignedCount, 4)
                                : 0,
                            'avg_days_to_complete' => $this->computeStepAvgDays($step),
                        ];
                    })->toArray(),
                    'velocity' => $velocity,
                ];
            })
            ->toArray();
    }

    /**
     * Compute avg/median days from assignment to completion for completed users.
     *
     * @return array{avg: float|null, median: float|null}
     */
    protected function computePathVelocity(ActivationPath $path): array
    {
        $completedPivots = $path->users
            ->filter(fn ($u) => $u->pivot->completed_at !== null && $u->pivot->assigned_at !== null)
            ->map(function ($u) {
                $assigned = \Carbon\Carbon::parse($u->pivot->assigned_at);
                $completed = \Carbon\Carbon::parse($u->pivot->completed_at);

                return max(0, $assigned->diffInDays($completed));
            })
            ->sort()
            ->values();

        if ($completedPivots->isEmpty()) {
            return ['avg' => null, 'median' => null];
        }

        return [
            'avg' => round($completedPivots->avg(), 1),
            'median' => round($this->percentile($completedPivots, 50), 1),
        ];
    }

    /**
     * Compute phase-level metrics (Tell/Show/Do).
     *
     * @return array<string, array{total_steps: int, completed_users: int, avg_days: float|null}>
     */
    protected function computePhaseMetrics(ActivationPath $path): array
    {
        $phases = [];

        foreach (['tell', 'show', 'do'] as $phase) {
            $phaseSteps = $path->steps->filter(fn (ActivationStep $s) => $s->phase?->value === $phase);

            if ($phaseSteps->isEmpty()) {
                continue;
            }

            $completedUsers = $phaseSteps->min(
                fn (ActivationStep $s) => $s->allProgress->where('completed', true)->count()
            );

            $avgDaysValues = $phaseSteps
                ->map(fn (ActivationStep $s) => $this->computeStepAvgDays($s))
                ->filter(fn ($v) => $v !== null);

            $phases[$phase] = [
                'total_steps' => $phaseSteps->count(),
                'completed_users' => $completedUsers,
                'avg_days' => $avgDaysValues->isNotEmpty()
                    ? round($avgDaysValues->avg(), 1)
                    : null,
            ];
        }

        return $phases;
    }

    /**
     * Compute average days to complete a single step.
     */
    protected function computeStepAvgDays(ActivationStep $step): ?float
    {
        $completedProgress = $step->allProgress
            ->filter(fn (StepProgress $p) => $p->completed && $p->completed_at !== null && $p->created_at !== null);

        if ($completedProgress->isEmpty()) {
            return null;
        }

        $days = $completedProgress->map(function (StepProgress $p) {
            return max(0, $p->created_at->diffInDays($p->completed_at));
        });

        return round($days->avg(), 1);
    }

    /**
     * Compute recent completion velocity for a path.
     *
     * @return array{completions_last_7d: int, completions_last_30d: int}
     */
    protected function computeRecentVelocity(ActivationPath $path): array
    {
        $recentCompletions = $path->users
            ->filter(fn ($u) => $u->pivot->completed_at !== null);

        return [
            'completions_last_7d' => $recentCompletions
                ->filter(fn ($u) => \Carbon\Carbon::parse($u->pivot->completed_at)->gte(now()->subDays(7)))
                ->count(),
            'completions_last_30d' => $recentCompletions
                ->filter(fn ($u) => \Carbon\Carbon::parse($u->pivot->completed_at)->gte(now()->subDays(30)))
                ->count(),
        ];
    }

    /**
     * Build instance-level summary from path metrics.
     *
     * @param  array<int, array<string, mixed>>  $paths
     * @return array<string, mixed>
     */
    protected function buildSummary(array $paths): array
    {
        $pathCollection = collect($paths);

        $completionRates = $pathCollection->pluck('completion_rate');
        $velocities = $pathCollection->pluck('avg_days_to_complete')->filter(fn ($v) => $v !== null);

        $usersInTraining = DB::table('activation_path_user')
            ->whereNull('completed_at')
            ->distinct('user_id')
            ->count('user_id');

        // Identify bottleneck steps (lowest completion rates, min 1 user assigned)
        $allSteps = $pathCollection->flatMap(function ($path) {
            return collect($path['steps'])->map(function ($step) use ($path) {
                $step['path'] = $path['feature'];

                return $step;
            });
        })
            ->filter(fn ($s) => $s['required'])
            ->sortBy('completion_rate')
            ->take(5)
            ->values()
            ->map(fn ($s) => [
                'path' => $s['path'],
                'step' => $s['name'],
                'phase' => $s['phase'],
                'completion_rate' => $s['completion_rate'],
            ])
            ->toArray();

        // Compute avg phase durations across all paths
        $phaseDurations = [];
        foreach (['tell', 'show', 'do'] as $phase) {
            $values = $pathCollection
                ->pluck("phases.{$phase}.avg_days")
                ->filter(fn ($v) => $v !== null);

            if ($values->isNotEmpty()) {
                $phaseDurations[$phase] = round($values->avg(), 1);
            }
        }

        return [
            'total_paths' => $pathCollection->count(),
            'active_paths' => $pathCollection->where('instance_active', true)->count(),
            'overall_completion_rate' => $completionRates->isNotEmpty()
                ? round($completionRates->avg(), 4)
                : 0,
            'avg_velocity_days' => $velocities->isNotEmpty()
                ? round($velocities->avg(), 1)
                : null,
            'users_in_training' => $usersInTraining,
            'features_fully_activated' => $pathCollection->where('instance_active', true)->count(),
            'avg_phase_days' => $phaseDurations,
            'bottleneck_steps' => $allSteps,
        ];
    }

    /**
     * Compute a percentile value from a sorted collection.
     */
    protected function percentile(Collection $sorted, int $percentile): float
    {
        $count = $sorted->count();

        if ($count === 0) {
            return 0;
        }

        if ($count === 1) {
            return (float) $sorted->first();
        }

        $rank = ($percentile / 100) * ($count - 1);
        $lower = (int) floor($rank);
        $upper = (int) ceil($rank);
        $fraction = $rank - $lower;

        if ($lower === $upper) {
            return (float) $sorted[$lower];
        }

        return $sorted[$lower] + $fraction * ($sorted[$upper] - $sorted[$lower]);
    }
}
