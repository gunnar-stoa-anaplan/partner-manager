<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreJourneyAgentLogRequest;
use App\Http\Requests\Api\V1\StoreJourneyParticipantRequest;
use App\Http\Requests\Api\V1\StoreJourneyPendingActionRequest;
use App\Http\Requests\Api\V1\TransitionJourneyStageRequest;
use App\Http\Requests\Api\V1\UpdateJourneyParticipantRequest;
use App\Http\Resources\V1\JourneyAgentLogResource;
use App\Http\Resources\V1\JourneyParticipantResource;
use App\Http\Resources\V1\JourneyPendingActionResource;
use App\Http\Resources\V1\JourneyStageHistoryResource;
use App\Models\Journey;
use App\Models\JourneyAgentLog;
use App\Models\JourneyParticipant;
use App\Models\JourneyPendingAction;
use App\Models\JourneyStageHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class JourneyRuntimeController extends Controller
{
    public function listParticipants(Journey $journey): AnonymousResourceCollection
    {
        $participants = $journey->participants()
            ->orderBy('name')
            ->get();

        return JourneyParticipantResource::collection($participants);
    }

    public function storeParticipant(StoreJourneyParticipantRequest $request, Journey $journey): JsonResponse
    {
        $data = $request->validated();
        $stage = $data['current_stage'] ?? 1;

        $participant = DB::transaction(function () use ($journey, $data, $stage) {
            $existing = isset($data['external_id'])
                ? $journey->participants()->where('external_id', $data['external_id'])->first()
                : null;

            if ($existing) {
                $existing->fill([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? $existing->email,
                    'last_active_at' => now(),
                    'metadata' => $data['metadata'] ?? $existing->metadata,
                ])->save();

                return $existing;
            }

            $participant = $journey->participants()->create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'external_id' => $data['external_id'] ?? null,
                'current_stage' => $stage,
                'enrolled_at' => now(),
                'last_active_at' => now(),
                'metadata' => $data['metadata'] ?? null,
            ]);

            JourneyStageHistory::create([
                'journey_participant_id' => $participant->id,
                'from_stage' => null,
                'to_stage' => $stage,
                'changed_by' => 'enrollment',
                'note' => 'Initial enrollment',
                'changed_at' => now(),
            ]);

            return $participant;
        });

        return (new JourneyParticipantResource($participant))
            ->response()
            ->setStatusCode(201);
    }

    public function showParticipant(Journey $journey, JourneyParticipant $participant): JsonResource
    {
        $this->ensureBelongs($journey, $participant);

        return new JourneyParticipantResource($participant);
    }

    public function updateParticipant(
        UpdateJourneyParticipantRequest $request,
        Journey $journey,
        JourneyParticipant $participant,
    ): JsonResource {
        $this->ensureBelongs($journey, $participant);

        $participant->fill($request->validated())->save();

        return new JourneyParticipantResource($participant);
    }

    public function transitionStage(
        TransitionJourneyStageRequest $request,
        Journey $journey,
        JourneyParticipant $participant,
    ): JsonResource {
        $this->ensureBelongs($journey, $participant);

        $data = $request->validated();
        $fromStage = $participant->current_stage;
        $toStage = $data['to_stage'];

        DB::transaction(function () use ($participant, $fromStage, $toStage, $data) {
            $participant->update([
                'current_stage' => $toStage,
                'last_active_at' => now(),
            ]);

            JourneyStageHistory::create([
                'journey_participant_id' => $participant->id,
                'from_stage' => $fromStage,
                'to_stage' => $toStage,
                'changed_by' => $data['changed_by'],
                'note' => $data['note'] ?? null,
                'changed_at' => now(),
            ]);
        });

        return new JourneyParticipantResource($participant->fresh());
    }

    public function participantHistory(Journey $journey, JourneyParticipant $participant): AnonymousResourceCollection
    {
        $this->ensureBelongs($journey, $participant);

        $history = $participant->stageHistory()
            ->orderByDesc('changed_at')
            ->get();

        return JourneyStageHistoryResource::collection($history);
    }

    public function listLogs(Journey $journey): AnonymousResourceCollection
    {
        $limit = (int) request()->query('limit', 30);
        $logs = $journey->agentLogs()
            ->orderByDesc('created_at')
            ->limit(min(max($limit, 1), 200))
            ->get();

        return JourneyAgentLogResource::collection($logs);
    }

    public function storeLog(StoreJourneyAgentLogRequest $request, Journey $journey): JsonResponse
    {
        $log = JourneyAgentLog::create([
            ...$request->validated(),
            'journey_id' => $journey->id,
            'level' => $request->validated()['level'] ?? 'info',
            'created_at' => now(),
        ]);

        return (new JourneyAgentLogResource($log))
            ->response()
            ->setStatusCode(201);
    }

    public function listPendingActions(Journey $journey): AnonymousResourceCollection
    {
        $actions = $journey->pendingActions()
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderByDesc('created_at')
            ->get();

        return JourneyPendingActionResource::collection($actions);
    }

    public function storePendingAction(StoreJourneyPendingActionRequest $request, Journey $journey): JsonResponse
    {
        $action = $journey->pendingActions()->create([
            ...$request->validated(),
            'assigned_to' => $request->validated()['assigned_to'] ?? 'agent',
            'status' => 'pending',
        ]);

        return (new JourneyPendingActionResource($action))
            ->response()
            ->setStatusCode(201);
    }

    public function completePendingAction(Journey $journey, JourneyPendingAction $action): JsonResource
    {
        $this->ensurePendingActionBelongs($journey, $action);

        $action->update([
            'status' => 'complete',
            'completed_at' => now(),
        ]);

        return new JourneyPendingActionResource($action);
    }

    private function ensureBelongs(Journey $journey, JourneyParticipant $participant): void
    {
        if ($participant->journey_id !== $journey->id) {
            abort(404, 'Participant not found in this journey.');
        }
    }

    private function ensurePendingActionBelongs(Journey $journey, JourneyPendingAction $action): void
    {
        if ($action->journey_id !== $journey->id) {
            abort(404, 'Pending action not found in this journey.');
        }
    }
}
