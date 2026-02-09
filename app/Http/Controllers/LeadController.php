<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Partner;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with('assignedPartner')->latest()->get();
        return view('business-development.leads.index', compact('leads'));
    }

    public function create()
    {
        $partners = Partner::all();
        return view('business-development.leads.create', compact('partners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'source' => 'required|string',
            'status' => 'required|string|in:assigned,unassigned,rejected',
            'assigned_partner_id' => 'nullable|exists:partners,id',
        ]);

        Lead::create($validated);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        return view('business-development.leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $partners = Partner::all();
        return view('business-development.leads.edit', compact('lead', 'partners'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'source' => 'required|string',
            'status' => 'required|string|in:assigned,unassigned,rejected',
            'assigned_partner_id' => 'nullable|exists:partners,id',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    public function unassigned()
    {
        $leads = Lead::where('status', 'Unassigned')->get();
        return view('business-development.leads.unassigned', compact('leads'));
    }
    
    public function assigned()
    {
        $leads = Lead::where('status', 'Assigned')->get();
        return view('business-development.leads.assigned', compact('leads'));
    }
    
    public function rejected()
    {
        $leads = Lead::where('status', 'Rejected')->get();
        return view('business-development.leads.rejected', compact('leads'));
    }
}