<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SupportCase;
use Illuminate\Support\Facades\Auth;

class PortalProjectsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        if ($contact->dashboardGroup() === 'admin') {
            // Admin group sees all partner projects
            $projects = Project::where('partner_id', $partner->id)
                ->orderByDesc('created_at')
                ->get();
        } else {
            // Delivery/other roles see only their assigned projects
            $projects = $contact->projects()
                ->where('partner_id', $partner->id)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('portal.projects.index', compact('contact', 'partner', 'projects'));
    }

    public function show(Project $project): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();

        // Ensure project belongs to this partner
        abort_unless($project->partner_id === $contact->partner_id, 403);

        // Non-admin contacts can only see projects they're assigned to
        if ($contact->dashboardGroup() !== 'admin') {
            abort_unless($contact->projects()->where('projects.id', $project->id)->exists(), 403);
        }

        $cases = SupportCase::where('project_id', $project->id)
            ->orderByDesc('opened_date')
            ->get();

        $team = $project->contacts ?? collect();

        return view('portal.projects.show', compact('contact', 'project', 'cases', 'team'));
    }
}
