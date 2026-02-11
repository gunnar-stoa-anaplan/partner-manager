<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Partner;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects for the authenticated user's partners.
     */
    public function index()
    {
        $user = auth()->user();

        // Admin sees all projects; PSM sees only their assigned partners' projects
        if ($user->role === 'admin') {
            $partnerIds = Partner::pluck('id');
        } else {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
        }

        $projects = Project::with(['partner', 'account', 'contacts', 'cal', 'tal', 'ae'])
            ->whereIn('partner_id', $partnerIds)
            ->orderBy('status')
            ->orderByDesc('start_date')
            ->get();

        // Aggregate stats for the header cards
        $totalAcv = $projects->sum('acv');
        $avgCompletion = $projects->count() > 0 ? round($projects->avg('percent_complete')) : 0;
        $activeCount = $projects->whereNotIn('status', ['Completed'])->count();
        $completedCount = $projects->where('status', 'Completed')->count();

        // Chart data: status distribution
        $statusDistribution = $projects->groupBy('status')->map->count()->sortDesc();

        // Chart data: ACV by partner (top 10)
        $acvByPartner = $projects->groupBy(fn($p) => $p->partner->name ?? 'Unknown')
            ->map(fn($group) => round($group->sum('acv'), 2))
            ->sortDesc()
            ->take(10);

        // Chart data: completion distribution (buckets)
        $completionBuckets = [
            '0-25%' => $projects->whereBetween('percent_complete', [0, 25])->count(),
            '26-50%' => $projects->whereBetween('percent_complete', [26, 50])->count(),
            '51-75%' => $projects->whereBetween('percent_complete', [51, 75])->count(),
            '76-100%' => $projects->whereBetween('percent_complete', [76, 100])->count(),
        ];

        return view('projects.index', compact(
            'projects', 'totalAcv', 'avgCompletion', 'activeCount', 'completedCount',
            'statusDistribution', 'acvByPartner', 'completionBuckets'
        ));
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $project->load(['partner', 'account', 'contacts', 'cal', 'tal', 'ae']);

        // Authorization: PSM can only view their own partners' projects
        $user = auth()->user();
        if ($user->role !== 'admin' && $project->partner->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this project.');
        }

        return view('projects.show', compact('project'));
    }

    /**
     * Display project risk analysis dashboard.
     */
    public function risks()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $partnerIds = Partner::pluck('id');
        } else {
            $partnerIds = Partner::where('user_id', $user->id)->pluck('id');
        }

        $projects = Project::with(['partner', 'account', 'contacts', 'cal', 'tal', 'ae'])
            ->whereIn('partner_id', $partnerIds)
            ->where('status', '!=', 'Completed')
            ->get();

        $now = Carbon::now();

        // Compute risk score for each project
        $riskedProjects = $projects->map(function ($project) use ($now) {
            $risks = [];
            $score = 0;

            // Schedule risk: behind expected pace
            if ($project->end_date && $project->start_date) {
                $totalDuration = $project->start_date->diffInDays($project->end_date);
                $elapsed = $project->start_date->diffInDays($now);
                if ($totalDuration > 0 && $elapsed > 0) {
                    $expectedPct = min(100, round(($elapsed / $totalDuration) * 100));
                    $actualPct = $project->percent_complete;
                    $gap = $expectedPct - $actualPct;
                    if ($gap > 30) {
                        $risks[] = ['type' => 'Schedule', 'detail' => "Behind by {$gap}pp ({$actualPct}% vs {$expectedPct}% expected)", 'severity' => 'critical'];
                        $score += 40;
                    } elseif ($gap > 15) {
                        $risks[] = ['type' => 'Schedule', 'detail' => "Trailing by {$gap}pp", 'severity' => 'high'];
                        $score += 25;
                    } elseif ($gap > 5) {
                        $risks[] = ['type' => 'Schedule', 'detail' => "Slightly behind ({$gap}pp gap)", 'severity' => 'medium'];
                        $score += 10;
                    }
                }
                // Overdue
                if ($project->end_date->isPast() && $project->percent_complete < 100) {
                    $overdueDays = $project->end_date->diffInDays($now);
                    $risks[] = ['type' => 'Overdue', 'detail' => "{$overdueDays} days past end date", 'severity' => 'critical'];
                    $score += 35;
                }
            }

            // Sprint risk: behind on sprints
            if ($project->total_sprints > 0 && $project->end_date && $project->start_date) {
                $totalDuration = $project->start_date->diffInDays($project->end_date);
                $elapsed = $project->start_date->diffInDays($now);
                if ($totalDuration > 0) {
                    $expectedSprint = round(($elapsed / $totalDuration) * $project->total_sprints);
                    $sprintGap = $expectedSprint - $project->current_sprint;
                    if ($sprintGap >= 2) {
                        $risks[] = ['type' => 'Sprint', 'detail' => "{$sprintGap} sprints behind (sprint {$project->current_sprint}/{$project->total_sprints})", 'severity' => 'high'];
                        $score += 20;
                    }
                }
            }

            // Stale risk: no update in 14+ days
            if ($project->updated_at->diffInDays($now) > 14) {
                $staleDays = $project->updated_at->diffInDays($now);
                $severity = $staleDays > 30 ? 'high' : 'medium';
                $risks[] = ['type' => 'Stale', 'detail' => "No updates for {$staleDays} days", 'severity' => $severity];
                $score += $staleDays > 30 ? 20 : 10;
            }

            // Resource risk: missing key roles
            $missingRoles = [];
            if (!$project->cal_id) $missingRoles[] = 'CAL';
            if (!$project->tal_id) $missingRoles[] = 'TAL';
            if (!$project->ae_id) $missingRoles[] = 'AE';
            if (count($missingRoles) > 0) {
                $severity = count($missingRoles) >= 2 ? 'high' : 'medium';
                $risks[] = ['type' => 'Resource', 'detail' => 'Missing: ' . implode(', ', $missingRoles), 'severity' => $severity];
                $score += count($missingRoles) * 8;
            }

            // No team contacts
            if ($project->contacts->isEmpty()) {
                $risks[] = ['type' => 'Resource', 'detail' => 'No team contacts assigned', 'severity' => 'high'];
                $score += 15;
            }

            // On Hold risk
            if ($project->status === 'On Hold') {
                $risks[] = ['type' => 'Status', 'detail' => 'Project is on hold', 'severity' => 'medium'];
                $score += 15;
            }

            // Low completion with high ACV
            if ($project->acv >= 100000 && $project->percent_complete < 25) {
                $risks[] = ['type' => 'Financial', 'detail' => 'High ACV ($' . number_format($project->acv) . ') with low completion', 'severity' => 'high'];
                $score += 20;
            }

            $project->risk_score = min(100, $score);
            $project->risks = $risks;
            $project->risk_level = $score >= 60 ? 'Critical' : ($score >= 35 ? 'High' : ($score >= 15 ? 'Medium' : 'Low'));

            return $project;
        })->sortByDesc('risk_score')->values();

        // Summary stats
        $totalAtRisk = $riskedProjects->where('risk_score', '>', 0)->count();
        $criticalCount = $riskedProjects->where('risk_level', 'Critical')->count();
        $highCount = $riskedProjects->where('risk_level', 'High')->count();
        $mediumCount = $riskedProjects->where('risk_level', 'Medium')->count();
        $totalExposedAcv = $riskedProjects->where('risk_score', '>=', 35)->sum('acv');

        // Risk level distribution
        $riskDistribution = [
            'Critical' => $criticalCount,
            'High' => $highCount,
            'Medium' => $mediumCount,
            'Low' => $riskedProjects->where('risk_level', 'Low')->count(),
        ];

        // Risk type breakdown (all individual risks flattened)
        $allRisks = $riskedProjects->flatMap(fn($p) => collect($p->risks));
        $riskTypeBreakdown = $allRisks->groupBy('type')->map->count()->sortDesc();

        // Risk by partner
        $riskByPartner = $riskedProjects->where('risk_score', '>', 0)
            ->groupBy(fn($p) => $p->partner->name ?? 'Unknown')
            ->map(fn($group) => round($group->avg('risk_score')))
            ->sortDesc()
            ->take(10);

        // At-risk ACV by partner
        $atRiskAcvByPartner = $riskedProjects->where('risk_score', '>=', 35)
            ->groupBy(fn($p) => $p->partner->name ?? 'Unknown')
            ->map(fn($group) => round($group->sum('acv'), 2))
            ->sortDesc()
            ->take(10);

        return view('projects.risks', compact(
            'riskedProjects', 'totalAtRisk', 'criticalCount', 'highCount', 'mediumCount',
            'totalExposedAcv', 'riskDistribution', 'riskTypeBreakdown',
            'riskByPartner', 'atRiskAcvByPartner'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
