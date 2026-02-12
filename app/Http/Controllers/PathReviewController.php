<?php
namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerPathEnrollment;
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
}
