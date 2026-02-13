<?php

namespace App\Http\Controllers;

use App\Models\GoalTemplate;
use App\Models\PartnerAssignedGoal;
use Illuminate\Http\Request;

class GoalTemplateController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $templates = GoalTemplate::with('creator')
            ->withCount('assignedGoals')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = GoalTemplate::CATEGORIES;
        $metricKeys = GoalTemplate::METRIC_KEYS;
        $units = GoalTemplate::UNITS;
        $cadences = GoalTemplate::CADENCES;

        return view('admin.goal-templates.index', compact('templates', 'categories', 'metricKeys', 'units', 'cadences'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::CATEGORIES)),
            'metric_key' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::METRIC_KEYS)),
            'default_target' => 'nullable|numeric|min:0',
            'unit' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::UNITS)),
            'cadence' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::CADENCES)),
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_common'] = true;

        GoalTemplate::create($validated);

        return redirect()->route('admin.goal-templates.index')->with('success', 'Goal template created.');
    }

    public function update(Request $request, GoalTemplate $template): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::CATEGORIES)),
            'metric_key' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::METRIC_KEYS)),
            'default_target' => 'nullable|numeric|min:0',
            'unit' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::UNITS)),
            'cadence' => 'required|string|in:'.implode(',', array_keys(GoalTemplate::CADENCES)),
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template->update($validated);

        return redirect()->route('admin.goal-templates.index')->with('success', 'Goal template updated.');
    }

    public function destroy(GoalTemplate $template): \Illuminate\Http\RedirectResponse
    {
        $template->delete();

        return redirect()->route('admin.goal-templates.index')->with('success', 'Goal template deleted.');
    }

    /**
     * Bulk assign a goal template to multiple partners.
     */
    public function assign(Request $request, GoalTemplate $template): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'partner_ids' => 'required|array',
            'partner_ids.*' => 'exists:partners,id',
            'fiscal_year' => 'required|string|max:10',
            'target_value' => 'required|numeric|min:0',
        ]);

        $count = 0;
        foreach ($validated['partner_ids'] as $partnerId) {
            $existing = PartnerAssignedGoal::where('partner_id', $partnerId)
                ->where('goal_template_id', $template->id)
                ->where('fiscal_year', $validated['fiscal_year'])
                ->exists();

            if (! $existing) {
                PartnerAssignedGoal::create([
                    'partner_id' => $partnerId,
                    'goal_template_id' => $template->id,
                    'name' => $template->name,
                    'category' => $template->category,
                    'metric_key' => $template->metric_key,
                    'unit' => $template->unit,
                    'cadence' => $template->cadence,
                    'fiscal_year' => $validated['fiscal_year'],
                    'target_value' => $validated['target_value'],
                    'assigned_by' => auth()->id(),
                ]);
                $count++;
            }
        }

        return redirect()->back()->with('success', "Goal assigned to {$count} partner(s).");
    }
}
