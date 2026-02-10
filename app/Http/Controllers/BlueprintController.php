<?php

namespace App\Http\Controllers;

use App\Models\Blueprint;
use App\Models\BlueprintOutcome;
use App\Models\BlueprintContentItem;
use Illuminate\Http\Request;

class BlueprintController extends Controller
{
    public function index()
    {
        $blueprints = Blueprint::withCount(['outcomes', 'assignments'])
            ->with('outcomes.contentItems')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('admin.blueprints.index', compact('blueprints'));
    }

    public function create()
    {
        return view('admin.blueprints.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:application,product',
            'product_area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'status' => 'required|in:draft,published,archived',
        ]);

        $validated['created_by'] = auth()->id();

        $blueprint = Blueprint::create($validated);

        return redirect()->route('blueprints.show', $blueprint)
            ->with('success', 'Blueprint created successfully.');
    }

    public function show(Blueprint $blueprint)
    {
        $blueprint->load(['outcomes.contentItems', 'assignments.partner', 'assignments.enrollments.contact']);

        return view('admin.blueprints.show', compact('blueprint'));
    }

    public function edit(Blueprint $blueprint)
    {
        $blueprint->load('outcomes.contentItems');

        return view('admin.blueprints.edit', compact('blueprint'));
    }

    public function update(Request $request, Blueprint $blueprint)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:application,product',
            'product_area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'status' => 'required|in:draft,published,archived',
        ]);

        $blueprint->update($validated);

        return redirect()->route('blueprints.show', $blueprint)
            ->with('success', 'Blueprint updated successfully.');
    }

    public function destroy(Blueprint $blueprint)
    {
        $blueprint->delete();

        return redirect()->route('blueprints.index')
            ->with('success', 'Blueprint deleted successfully.');
    }

    // ─── Outcome Management ────────────────────────────────────

    public function addOutcome(Request $request, Blueprint $blueprint)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'required|integer|min:1',
        ]);

        $validated['blueprint_id'] = $blueprint->id;

        BlueprintOutcome::create($validated);

        return redirect()->route('blueprints.edit', $blueprint)
            ->with('success', 'Outcome added successfully.');
    }

    public function updateOutcome(Request $request, BlueprintOutcome $outcome)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'required|integer|min:1',
        ]);

        $outcome->update($validated);

        return redirect()->route('blueprints.edit', $outcome->blueprint_id)
            ->with('success', 'Outcome updated successfully.');
    }

    public function destroyOutcome(BlueprintOutcome $outcome)
    {
        $blueprintId = $outcome->blueprint_id;
        $outcome->delete();

        return redirect()->route('blueprints.edit', $blueprintId)
            ->with('success', 'Outcome deleted successfully.');
    }

    // ─── Content Item Management ───────────────────────────────

    public function addContentItem(Request $request, BlueprintOutcome $outcome)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:2048',
            'duration_minutes' => 'nullable|integer|min:0',
            'duration_display' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
            'prerequisites' => 'nullable|string|max:500',
            'gtm_applicable' => 'required|in:required,optional,na',
            'pre_sales_applicable' => 'required|in:required,optional,na',
            'delivery_applicable' => 'required|in:required,optional,na',
        ]);

        $validated['blueprint_outcome_id'] = $outcome->id;
        $validated['sort_order'] = $outcome->contentItems()->max('sort_order') + 1;

        BlueprintContentItem::create($validated);

        return redirect()->route('blueprints.edit', $outcome->blueprint_id)
            ->with('success', 'Content item added successfully.');
    }

    public function updateContentItem(Request $request, BlueprintContentItem $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:2048',
            'duration_minutes' => 'nullable|integer|min:0',
            'duration_display' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:100',
            'prerequisites' => 'nullable|string|max:500',
            'gtm_applicable' => 'required|in:required,optional,na',
            'pre_sales_applicable' => 'required|in:required,optional,na',
            'delivery_applicable' => 'required|in:required,optional,na',
        ]);

        $item->update($validated);

        return redirect()->route('blueprints.edit', $item->outcome->blueprint_id)
            ->with('success', 'Content item updated successfully.');
    }

    public function destroyContentItem(BlueprintContentItem $item)
    {
        $blueprintId = $item->outcome->blueprint_id;
        $item->delete();

        return redirect()->route('blueprints.edit', $blueprintId)
            ->with('success', 'Content item deleted successfully.');
    }
}
