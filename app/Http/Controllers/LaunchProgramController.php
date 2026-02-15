<?php

namespace App\Http\Controllers;

use App\Models\LaunchProgram;
use App\Models\LaunchProgramMilestone;
use App\Models\LaunchProgramPartner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LaunchProgramController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $programs = LaunchProgram::with(['product', 'creator'])
            ->withCount(['programPartners', 'milestones'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.launch-programs.index', compact('programs'));
    }

    public function create(): \Illuminate\View\View
    {
        $products = Product::published()->orderBy('name')->get();

        return view('admin.launch-programs.create', compact('products'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgram::STATUSES))],
            'starts_at' => ['nullable', 'date'],
            'target_ga_date' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'max_partners' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = auth()->id();

        $program = LaunchProgram::create($validated);

        return redirect()->route('admin.launch-programs.show', $program)
            ->with('success', 'Launch program created successfully.');
    }

    public function show(LaunchProgram $launchProgram): \Illuminate\View\View
    {
        $launchProgram->load([
            'product',
            'creator',
            'milestones',
            'programPartners.partner',
            'programPartners.participants.contact',
            'programPartners.participants.milestoneCompletions',
            'programPartners.feedback.contact',
        ]);

        return view('admin.launch-programs.show', ['program' => $launchProgram]);
    }

    public function edit(LaunchProgram $launchProgram): \Illuminate\View\View
    {
        $products = Product::published()->orderBy('name')->get();

        return view('admin.launch-programs.edit', [
            'program' => $launchProgram,
            'products' => $products,
        ]);
    }

    public function update(Request $request, LaunchProgram $launchProgram): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgram::STATUSES))],
            'starts_at' => ['nullable', 'date'],
            'target_ga_date' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'max_partners' => ['nullable', 'integer', 'min:1'],
        ]);

        $launchProgram->update($validated);

        return redirect()->route('admin.launch-programs.show', $launchProgram)
            ->with('success', 'Launch program updated successfully.');
    }

    public function destroy(LaunchProgram $launchProgram): \Illuminate\Http\RedirectResponse
    {
        if ($launchProgram->status !== 'draft') {
            return back()->with('error', 'Only draft programs can be deleted.');
        }

        $launchProgram->delete();

        return redirect()->route('admin.launch-programs.index')
            ->with('success', 'Launch program deleted successfully.');
    }

    public function addMilestone(Request $request, LaunchProgram $launchProgram): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'is_required' => ['boolean'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgramMilestone::TYPES))],
        ]);

        $validated['sort_order'] = $launchProgram->milestones()->max('sort_order') + 1;

        $launchProgram->milestones()->create($validated);

        return back()->with('success', 'Milestone added successfully.');
    }

    public function updateMilestone(Request $request, LaunchProgramMilestone $milestone): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'is_required' => ['boolean'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgramMilestone::TYPES))],
        ]);

        $milestone->update($validated);

        return back()->with('success', 'Milestone updated successfully.');
    }

    public function destroyMilestone(LaunchProgramMilestone $milestone): \Illuminate\Http\RedirectResponse
    {
        $milestone->delete();

        return back()->with('success', 'Milestone removed.');
    }

    public function updatePartnerStatus(Request $request, LaunchProgramPartner $programPartner): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgramPartner::STATUSES))],
        ]);

        $programPartner->update([
            'status' => $validated['status'],
            'accepted_at' => $validated['status'] === 'accepted' ? now() : $programPartner->accepted_at,
        ]);

        return back()->with('success', 'Partner status updated.');
    }
}
