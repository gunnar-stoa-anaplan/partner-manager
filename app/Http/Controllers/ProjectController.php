<?php

namespace App\Http\Controllers;
use App\Models\Project;
use App\Models\Partner;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Hard-code your “current” user ID (the one you seeded)
        $userId = 11;

        // Get all partner IDs belonging to that user
        $partnerIds = Partner::where('user_id', $userId)
                             ->pluck('id')
                             ->toArray();

        // Load only those projects
        $projects = Project::with(['account', 'contacts'])
            ->whereIn('partner_id', $partnerIds)
            ->get();

        return view('projects.index', compact('projects'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load(['account', 'contacts']);
        return view('projects.show', compact('project'));
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
