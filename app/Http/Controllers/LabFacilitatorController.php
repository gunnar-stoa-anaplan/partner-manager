<?php

namespace App\Http\Controllers;

use App\Models\EnablementSession;
use App\Models\LabCheckpoint;
use App\Models\LabParticipant;
use App\Services\LabValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LabFacilitatorController extends Controller
{
    public function dashboard(EnablementSession $session): View
    {
        $session->load('event');

        $labs = $session->labs()
            ->where('status', 'published')
            ->with([
                'checkpoints',
                'participants.contact',
                'participants.partner',
                'participants.checkpointResults',
            ])
            ->get();

        // Build progress grid: participant → checkpoint → latest result
        $progressGrid = [];
        foreach ($labs as $lab) {
            $grid = [];
            foreach ($lab->participants as $participant) {
                $row = [
                    'participant' => $participant,
                    'checkpoints' => [],
                ];
                foreach ($lab->checkpoints as $checkpoint) {
                    $latestResult = $participant->checkpointResults
                        ->where('lab_checkpoint_id', $checkpoint->id)
                        ->sortByDesc('attempt_number')
                        ->first();

                    $row['checkpoints'][$checkpoint->id] = [
                        'status' => $latestResult ? ($latestResult->passed ? 'passed' : 'failed') : 'pending',
                        'attempts' => $participant->checkpointResults->where('lab_checkpoint_id', $checkpoint->id)->count(),
                        'error' => $latestResult?->error_message,
                    ];
                }
                $grid[] = $row;
            }
            $progressGrid[$lab->id] = $grid;
        }

        return view('enablement.lab-dashboard', compact('session', 'labs', 'progressGrid'));
    }

    public function validateAll(EnablementSession $session, LabValidationService $validationService): RedirectResponse
    {
        $labs = $session->labs()
            ->where('status', 'published')
            ->with(['checkpoints', 'participants.assignedModel'])
            ->get();

        $validated = 0;
        foreach ($labs as $lab) {
            foreach ($lab->participants as $participant) {
                if (! $participant->assignedModel) {
                    continue;
                }
                foreach ($lab->checkpoints as $checkpoint) {
                    $latestResult = $participant->latestResultFor($checkpoint);
                    if ($latestResult && $latestResult->passed) {
                        continue; // Already passed
                    }
                    $validationService->validateCheckpoint($participant, $checkpoint);
                    $validated++;
                }
            }
        }

        return redirect()->route('sessions.lab-dashboard', $session)
            ->with('success', "Validated {$validated} checkpoints across all participants.");
    }

    public function validateParticipant(
        LabParticipant $participant,
        LabCheckpoint $checkpoint,
        LabValidationService $validationService,
    ): JsonResponse {
        $result = $validationService->validateCheckpoint($participant, $checkpoint);

        return response()->json([
            'passed' => $result->passed,
            'error_message' => $result->error_message,
            'attempt_number' => $result->attempt_number,
        ]);
    }
}
