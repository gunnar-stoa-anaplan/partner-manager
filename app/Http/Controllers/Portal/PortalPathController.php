<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PartnerPathEnrollment;
use App\Models\PartnerStageHistory;
use App\Models\PartnerTask;
use App\Services\GateEvaluationService;
use Illuminate\Support\Facades\Auth;

class PortalPathController extends Controller
{
    public function index(GateEvaluationService $gateService): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $enrollment = PartnerPathEnrollment::with(['path.stages', 'currentStage'])
            ->where('partner_id', $partner->id)
            ->active()
            ->first();

        if (! $enrollment) {
            return view('portal.path.index', compact('contact', 'partner', 'enrollment'));
        }

        $stages = $enrollment->path->stages->sortBy('stage_number');
        $currentStageNumber = $enrollment->currentStage->stage_number;
        $totalStages = $stages->count();
        $stageProgress = $totalStages > 1
            ? round(($currentStageNumber - 1) / ($totalStages - 1) * 100)
            : 100;
        $daysInStage = (int) $enrollment->stage_entered_at->diffInDays(now());

        // Next stage and gate evaluation
        $nextStage = $stages->where('stage_number', '>', $currentStageNumber)->first();
        $gateResults = $nextStage
            ? $gateService->evaluateGates($partner, $enrollment->current_stage_id, $nextStage->id)
            : collect();
        $metCount = $gateResults->where('met', true)->count();
        $totalGates = $gateResults->count();
        $gateReadinessPct = $totalGates > 0 ? round(($metCount / $totalGates) * 100) : 100;

        // Task statistics
        $tasks = PartnerTask::where('partner_path_enrollment_id', $enrollment->id)
            ->with('stage')
            ->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->whereIn('status', ['completed', 'waived'])->count();

        $requiredTasks = $tasks->where('requirement_level', 'required');
        $requiredTotal = $requiredTasks->count();
        $requiredCompleted = $requiredTasks->where('status', 'completed')->count();
        $requiredCompletionPct = $requiredTotal > 0 ? round(($requiredCompleted / $requiredTotal) * 100) : 100;

        $overdueCount = $tasks->filter(fn ($t) => $t->isOverdue())->count();

        // Current stage tasks
        $currentStageTasks = $tasks->where('stage_id', $enrollment->current_stage_id)
            ->sortBy(fn ($t) => array_search($t->status, ['blocked', 'not_started', 'in_progress', 'completed', 'waived']));

        // Stage transition history
        $history = PartnerStageHistory::where('partner_id', $partner->id)
            ->where('partner_path_id', $enrollment->partner_path_id)
            ->with(['fromStage', 'toStage'])
            ->orderByDesc('transitioned_at')
            ->get();

        return view('portal.path.index', compact(
            'contact', 'partner', 'enrollment', 'stages', 'nextStage',
            'currentStageNumber', 'totalStages', 'stageProgress', 'daysInStage',
            'gateResults', 'metCount', 'totalGates', 'gateReadinessPct',
            'totalTasks', 'completedTasks',
            'requiredTotal', 'requiredCompleted', 'requiredCompletionPct',
            'overdueCount', 'currentStageTasks', 'history'
        ));
    }
}
