<?php

namespace App\Services;

use App\Models\EnablementGoal;
use App\Models\ResourceSnapshot;
use Illuminate\Support\Collection;

class EnablementGoalService
{
    /**
     * Compute actual completed counts per category for a partner on a given snapshot date.
     *
     * @return array<string, int> category_key => completed_count
     */
    public function actuals(int $partnerId, ?string $snapshotDate = null): array
    {
        if (! $snapshotDate) {
            $snapshotDate = ResourceSnapshot::where('partner_id', $partnerId)
                ->max('snapshot_date');
        }

        if (! $snapshotDate) {
            return $this->emptyCategoryMap();
        }

        $snapshots = ResourceSnapshot::where('partner_id', $partnerId)
            ->where('snapshot_date', $snapshotDate)
            ->get(['program', 'role', 'completed_count']);

        return $this->computeActualsFromRows($snapshots);
    }

    /**
     * Historical actuals across all snapshot dates for a set of partners.
     *
     * @return array<int, array{dates: string[], series: array<string, int[]>}> keyed by partner_id
     */
    public function historicalActuals(Collection|array $partnerIds): array
    {
        $allRows = ResourceSnapshot::whereIn('partner_id', $partnerIds)
            ->orderBy('snapshot_date')
            ->get(['partner_id', 'snapshot_date', 'program', 'role', 'completed_count']);

        $byPartner = $allRows->groupBy('partner_id');
        $emptyMap = $this->emptyCategoryMap();

        $result = [];
        foreach ($partnerIds as $pid) {
            $partnerRows = $byPartner[$pid] ?? collect();
            $byDate = $partnerRows->groupBy('snapshot_date');

            $dates = [];
            $seriesData = [];

            foreach ($byDate as $date => $rows) {
                $dates[] = $date;
                $actuals = $this->computeActualsFromRows($rows);

                foreach ($emptyMap as $key => $_) {
                    $seriesData[$key][] = $actuals[$key] ?? 0;
                }
            }

            $result[$pid] = [
                'dates' => $dates,
                'series' => $seriesData,
            ];
        }

        return $result;
    }

    /**
     * Build the full goals matrix for a set of partners (used on SoB page).
     *
     * @return array<int, array<string, array>> keyed by partner_id → group_key → lines/aggregate
     */
    public function goalsForPartners(Collection|array $partnerIds, int $userId, ?string $snapshotDate = null): array
    {
        $goals = EnablementGoal::where('user_id', $userId)
            ->whereIn('partner_id', $partnerIds)
            ->get()
            ->groupBy('partner_id');

        $result = [];
        foreach ($partnerIds as $pid) {
            $partnerGoals = ($goals[$pid] ?? collect())->keyBy('category');
            $partnerActuals = $this->actuals($pid, $snapshotDate);

            $groups = [];
            foreach (config('enablement_goals.groups', []) as $groupKey => $group) {
                if (! empty($group['aggregate'])) {
                    $goal = $partnerGoals[$groupKey] ?? null;
                    $actual = $partnerActuals[$groupKey] ?? 0;
                    $target = $goal?->target ?? 0;
                    $baseline = $goal?->baseline ?? 0;

                    $groups[$groupKey] = [
                        $groupKey => [
                            'label' => $group['label'],
                            'goal_id' => $goal?->id,
                            'baseline' => $baseline,
                            'target' => $target,
                            'actual' => $actual,
                            'variance' => $target > 0 ? $actual - $target : 0,
                        ],
                    ];
                } else {
                    $lines = [];
                    foreach ($group['lines'] as $lineKey => $line) {
                        $goal = $partnerGoals[$lineKey] ?? null;
                        $actual = $partnerActuals[$lineKey] ?? 0;
                        $target = $goal?->target ?? 0;
                        $baseline = $goal?->baseline ?? 0;

                        $lines[$lineKey] = [
                            'label' => $line['label'],
                            'goal_id' => $goal?->id,
                            'baseline' => $baseline,
                            'target' => $target,
                            'actual' => $actual,
                            'variance' => $target > 0 ? $actual - $target : 0,
                        ];
                    }
                    $groups[$groupKey] = $lines;
                }
            }

            $result[$pid] = $groups;
        }

        return $result;
    }

    /**
     * Compute category actuals from a collection of snapshot rows (single date).
     *
     * @return array<string, int>
     */
    private function computeActualsFromRows(Collection $rows): array
    {
        $result = [];
        foreach (config('enablement_goals.groups', []) as $groupKey => $group) {
            $role = $group['role'];

            if (! empty($group['aggregate'])) {
                $result[$groupKey] = $rows
                    ->where('role', $role)
                    ->sum('completed_count');
            } else {
                foreach ($group['lines'] as $lineKey => $line) {
                    if ($line['program'] === null) {
                        $result[$lineKey] = 0;
                    } else {
                        $match = $rows->first(fn ($row) => $row->role === $role && $row->program === $line['program']);
                        $result[$lineKey] = $match ? $match->completed_count : 0;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return array<string, int>
     */
    private function emptyCategoryMap(): array
    {
        $keys = [];
        foreach (config('enablement_goals.groups', []) as $groupKey => $group) {
            if (! empty($group['aggregate'])) {
                $keys[] = $groupKey;
            } else {
                foreach (array_keys($group['lines'] ?? []) as $lineKey) {
                    $keys[] = $lineKey;
                }
            }
        }

        return array_fill_keys($keys, 0);
    }
}
