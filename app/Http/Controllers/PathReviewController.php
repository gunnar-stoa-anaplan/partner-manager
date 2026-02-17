<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerPathStage;
use App\Services\GateEvaluationService;
use App\Services\TaskAutoPopulationService;
use Illuminate\Http\Request;

class PathReviewController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = PartnerPathEnrollment::with(['partner', 'currentStage', 'path'])
            ->where('status', 'active');

        if ($user->role !== 'admin') {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
            $query->whereIn('partner_id', $partnerIds);
        }

        $enrollments = $query->get();

        return view('act.path-reviews.index', compact('enrollments'));
    }

    public function show(PartnerPathEnrollment $enrollment, GateEvaluationService $gateService)
    {
        $enrollment->load(['partner', 'currentStage', 'path.stages']);

        $nextStage = $enrollment->path->stages
            ->where('stage_number', '>', $enrollment->currentStage->stage_number)
            ->sortBy('stage_number')->first();

        $gateResults = $nextStage
            ? $gateService->evaluateGates($enrollment->partner, $enrollment->current_stage_id, $nextStage->id)
            : collect();

        $totalGates = $gateResults->count();
        $metGates = $gateResults->where('met', true)->count();

        return view('act.path-reviews.show', compact('enrollment', 'nextStage', 'gateResults', 'totalGates', 'metGates'));
    }

    public function advance(PartnerPathEnrollment $enrollment, GateEvaluationService $gateService, TaskAutoPopulationService $taskService): \Illuminate\Http\RedirectResponse
    {
        $enrollment->load(['currentStage', 'partner']);

        $nextStage = PartnerPathStage::where('partner_path_id', $enrollment->partner_path_id)
            ->where('stage_number', '>', $enrollment->currentStage->stage_number)
            ->orderBy('stage_number')
            ->firstOrFail();

        $gateResults = $gateService->evaluateGates($enrollment->partner, $enrollment->current_stage_id, $nextStage->id);

        if (! $gateService->allGatesMet($enrollment->partner, $enrollment->current_stage_id, $nextStage->id)) {
            return redirect()->route('act.path-reviews.show', $enrollment)
                ->with('error', 'Not all gate requirements are met.');
        }

        $enrollment->partner->stageHistory()->create([
            'partner_path_id' => $enrollment->partner_path_id,
            'from_stage_id' => $enrollment->current_stage_id,
            'to_stage_id' => $nextStage->id,
            'transitioned_at' => now(),
            'approved_by' => auth()->id(),
            'gate_results' => $gateResults->toArray(),
        ]);

        $enrollment->update([
            'current_stage_id' => $nextStage->id,
            'stage_entered_at' => now(),
            'last_reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $taskService->populateTasksForEnrollment($enrollment->fresh());

        return redirect()->route('act.path-reviews.show', $enrollment)
            ->with('success', "Partner advanced to {$nextStage->name}.");
    }

    public function hold(Request $request, PartnerPathEnrollment $enrollment): \Illuminate\Http\RedirectResponse
    {
        $enrollment->update([
            'status' => 'paused',
            'last_reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'notes' => $request->input('notes', 'Placed on hold during path review.'),
        ]);

        return redirect()->route('act.path-reviews.index')
            ->with('success', "{$enrollment->partner->name} has been placed on hold.");
    }
}
