<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\LabCheckpoint;
use App\Services\LabValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalLabController extends Controller
{
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();

        // Find labs from sessions the contact is registered for
        $sessionIds = $contact->enablementRegistrations()
            ->whereIn('status', ['registered', 'confirmed', 'attended'])
            ->pluck('enablement_session_id');

        $labs = Lab::whereIn('enablement_session_id', $sessionIds)
            ->where('status', 'published')
            ->with(['session.event', 'checkpoints'])
            ->get();

        // Get participant records for this contact
        $participantMap = $contact->labParticipants()
            ->whereIn('lab_id', $labs->pluck('id'))
            ->get()
            ->keyBy('lab_id');

        return view('portal.labs.index', compact('labs', 'participantMap'));
    }

    public function show(Lab $lab): View
    {
        $contact = Auth::guard('portal')->user();

        $participant = $contact->labParticipants()
            ->where('lab_id', $lab->id)
            ->first();

        if (! $participant) {
            abort(403, 'You are not enrolled in this lab.');
        }

        $lab->load(['session.event', 'checkpoints']);
        $participant->load('checkpointResults');

        // Build checkpoint status for this participant
        $checkpointProgress = [];
        foreach ($lab->checkpoints as $checkpoint) {
            $latestResult = $participant->checkpointResults
                ->where('lab_checkpoint_id', $checkpoint->id)
                ->sortByDesc('attempt_number')
                ->first();

            $checkpointProgress[$checkpoint->id] = [
                'status' => $latestResult ? ($latestResult->passed ? 'passed' : 'failed') : 'pending',
                'attempts' => $participant->checkpointResults->where('lab_checkpoint_id', $checkpoint->id)->count(),
                'error_message' => $latestResult && ! $latestResult->passed ? $latestResult->error_message : null,
                'hint' => $checkpoint->hint,
            ];
        }

        return view('portal.labs.show', compact('lab', 'participant', 'checkpointProgress'));
    }

    public function validateCheckpoint(LabCheckpoint $checkpoint, LabValidationService $validationService): JsonResponse
    {
        $contact = Auth::guard('portal')->user();

        $participant = $contact->labParticipants()
            ->where('lab_id', $checkpoint->lab_id)
            ->first();

        if (! $participant) {
            return response()->json(['error' => 'You are not enrolled in this lab.'], 403);
        }

        if (! $participant->assignedModel) {
            return response()->json(['error' => 'No Anaplan model has been assigned to you yet.'], 422);
        }

        $result = $validationService->validateCheckpoint($participant, $checkpoint);

        return response()->json([
            'passed' => $result->passed,
            'error_message' => $result->error_message,
            'attempt_number' => $result->attempt_number,
            'hint' => ! $result->passed ? $checkpoint->hint : null,
            'completion_percentage' => $participant->fresh()->completionPercentage(),
        ]);
    }
}
