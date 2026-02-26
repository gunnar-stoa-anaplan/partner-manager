<?php

namespace App\Http\Controllers;

use App\Models\LaunchProgram;
use App\Models\LaunchProgramDateChange;
use App\Models\LaunchProgramMilestone;
use App\Models\LaunchProgramPartner;
use App\Models\Partner;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LaunchProgramController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $programs = LaunchProgram::with(['product', 'creator', 'owner'])
            ->withCount(['programPartners', 'milestones'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.launch-programs.index', compact('programs'));
    }

    public function create(): \Illuminate\View\View
    {
        $products = Product::published()->orderBy('name')->get();
        $psms = User::where('role', 'psm')->orderBy('name')->get();

        return view('admin.launch-programs.create', compact('products', 'psms'));
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
            'partner_strategy' => ['required', 'string', 'in:include,exclude'],
            'exclusion_reason' => ['nullable', 'string', 'required_if:partner_strategy,exclude'],
            'owner_id' => ['nullable', 'exists:users,id,role,psm'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = auth()->id();

        if ($validated['partner_strategy'] === 'include') {
            $validated['exclusion_reason'] = null;
        }

        $program = LaunchProgram::create($validated);

        return redirect()->route('admin.launch-programs.show', $program)
            ->with('success', 'Launch program created successfully.');
    }

    public function show(LaunchProgram $launchProgram): \Illuminate\View\View
    {
        $launchProgram->load([
            'product',
            'creator',
            'owner',
            'milestones',
            'programPartners.partner',
            'programPartners.participants.contact',
            'programPartners.participants.milestoneCompletions',
            'programPartners.feedback.contact',
            'dateChanges.changedBy',
        ]);

        $partners = Partner::orderBy('name')->get();

        return view('admin.launch-programs.show', [
            'program' => $launchProgram,
            'partners' => $partners,
        ]);
    }

    public function edit(LaunchProgram $launchProgram): \Illuminate\View\View
    {
        $products = Product::published()->orderBy('name')->get();
        $psms = User::where('role', 'psm')->orderBy('name')->get();

        return view('admin.launch-programs.edit', [
            'program' => $launchProgram,
            'products' => $products,
            'psms' => $psms,
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
            'partner_strategy' => ['required', 'string', 'in:include,exclude'],
            'exclusion_reason' => ['nullable', 'string', 'required_if:partner_strategy,exclude'],
            'owner_id' => ['nullable', 'exists:users,id,role,psm'],
            'date_change_reason' => ['nullable', 'string'],
        ]);

        if ($validated['partner_strategy'] === 'include') {
            $validated['exclusion_reason'] = null;
        }

        // Track date changes before updating
        $dateChangeReason = $validated['date_change_reason'] ?? null;
        unset($validated['date_change_reason']);

        $dateFields = ['starts_at', 'target_ga_date', 'ends_at'];
        $dateChanges = [];
        foreach ($dateFields as $field) {
            $oldValue = $launchProgram->getOriginal($field);
            $oldStr = $oldValue ? Carbon::parse($oldValue)->toDateString() : null;
            $newStr = ! empty($validated[$field]) ? Carbon::parse($validated[$field])->toDateString() : null;

            if ($oldStr !== $newStr) {
                $dateChanges[] = [
                    'launch_program_id' => $launchProgram->id,
                    'field' => $field,
                    'old_date' => $oldStr,
                    'new_date' => $newStr,
                    'reason' => $dateChangeReason,
                    'changed_by' => auth()->id(),
                ];
            }
        }

        $launchProgram->update($validated);

        foreach ($dateChanges as $change) {
            LaunchProgramDateChange::create($change);
        }

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
        $program = $programPartner->program;

        if (! auth()->user()->isAdmin() && auth()->id() !== $program->owner_id) {
            abort(403, 'Only the program owner or an admin can update partner status.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgramPartner::STATUSES))],
        ]);

        $programPartner->update([
            'status' => $validated['status'],
            'accepted_at' => $validated['status'] === 'accepted' ? now() : $programPartner->accepted_at,
        ]);

        return back()->with('success', 'Partner status updated.');
    }

    public function adminNominate(Request $request, LaunchProgram $launchProgram): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'partner_id' => ['required', 'exists:partners,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if (LaunchProgramPartner::where('launch_program_id', $launchProgram->id)
            ->where('partner_id', $validated['partner_id'])->exists()) {
            return back()->with('error', 'This partner is already enrolled in this program.');
        }

        LaunchProgramPartner::create([
            'launch_program_id' => $launchProgram->id,
            'partner_id' => $validated['partner_id'],
            'status' => 'nominated',
            'nominated_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Partner nominated successfully.');
    }
}
