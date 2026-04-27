<?php

namespace App\Services;

use App\Models\Journey;
use App\Models\JourneyStageHistory;
use Illuminate\Support\Collection;

class JourneyDatabase
{
    /**
     * Stage definitions — keyed by stage number (1-based).
     *
     * @var array<int, array{name: string, tasks: int}>
     */
    public const STAGES = [
        1 => ['name' => 'Registered', 'tasks' => 3],
        2 => ['name' => 'Trained', 'tasks' => 3],
        3 => ['name' => 'Credentialized', 'tasks' => 3],
        4 => ['name' => 'Delivering', 'tasks' => 3],
        5 => ['name' => 'Expert', 'tasks' => 3],
    ];

    public function __construct(private readonly Journey $journey) {}

    /**
     * @return Collection<int, \App\Models\JourneyParticipant>
     */
    public function participants(): Collection
    {
        return $this->journey->participants()
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\JourneyPendingAction>
     */
    public function pendingActions(): Collection
    {
        return $this->journey->pendingActions()
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\JourneyAgentLog>
     */
    public function agentLog(int $limit = 30): Collection
    {
        return $this->journey->agentLogs()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\JourneyStageHistory>
     */
    public function stageHistory(): Collection
    {
        return JourneyStageHistory::query()
            ->whereHas('participant', fn ($q) => $q->where('journey_id', $this->journey->id))
            ->orderByDesc('changed_at')
            ->get();
    }
}
