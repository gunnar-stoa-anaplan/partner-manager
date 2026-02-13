<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\SupportCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalCasesController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $cases = SupportCase::where('partner_id', $partner->id)
            ->with('project')
            ->orderByDesc('opened_date')
            ->paginate(20);

        $projects = Project::where('partner_id', $partner->id)
            ->orderBy('name')
            ->get();

        $openCount = SupportCase::where('partner_id', $partner->id)->open()->count();
        $escalatedCount = SupportCase::where('partner_id', $partner->id)->escalated()->count();

        return view('portal.cases.index', compact('contact', 'partner', 'cases', 'projects', 'openCount', 'escalatedCount'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'severity' => 'required|in:'.implode(',', array_keys(SupportCase::SEVERITIES)),
            'project_id' => 'nullable|exists:projects,id',
            'category' => 'nullable|string|max:255',
        ]);

        // Verify project belongs to this partner
        if ($validated['project_id'] ?? null) {
            $project = Project::find($validated['project_id']);
            abort_unless($project && $project->partner_id === $contact->partner_id, 403);
            $validated['account_name'] = $project->account_name ?? $contact->partner->name;
        }

        SupportCase::create([
            ...$validated,
            'partner_id' => $contact->partner_id,
            'status' => 'open',
            'opened_date' => now(),
            'case_number' => 'PC-'.str_pad(SupportCase::max('id') + 1, 6, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('portal.cases.index')
            ->with('success', 'Case logged successfully.');
    }
}
