<?php

namespace App\Http\Controllers;

use App\Models\ResourcePath;
use App\Models\ResourcePathGate;
use App\Models\ResourcePathStage;
use App\Models\ResourceTaskTemplate;
use Illuminate\Http\Request;

class ResourcePathConfigurationController extends Controller
{
    /**
     * Platform technologies that get their own path group.
     */
    private const PLATFORMS = ['Polaris', 'ADO', 'Forecaster', 'CoModeler', 'Agent Studio'];

    /**
     * Product groups for categorization.
     */
    private const PRODUCT_GROUPS = ['Finance', 'Supply Chain', 'HR & Workforce', 'Sales & Marketing'];

    public function index()
    {
        $paths = ResourcePath::withCount(['stages', 'gates', 'enrollments', 'taskTemplates'])
            ->with(['stages' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('name')
            ->get();

        // Categorize paths
        $generalTechnical = collect(['Model Builder', 'Solution Architect', 'Master Anaplanner']);
        $generalNonTechnical = collect(['Sales', 'Pre-Sales', 'Champion']);

        $categorized = [
            'general' => $paths->filter(fn ($p) => $generalTechnical->contains($p->name) || $generalNonTechnical->contains($p->name)),
            'platform' => $paths->filter(fn ($p) => collect(self::PLATFORMS)->contains(fn ($t) => str_starts_with($p->name, $t.':'))),
            'product_group' => $paths->filter(fn ($p) => collect(self::PRODUCT_GROUPS)->contains(fn ($g) => str_starts_with($p->name, $g.':'))),
            'application' => collect(),
        ];

        // Application paths = everything not in the above categories
        $nonAppIds = $categorized['general']->pluck('id')
            ->merge($categorized['platform']->pluck('id'))
            ->merge($categorized['product_group']->pluck('id'));
        $categorized['application'] = $paths->reject(fn ($p) => $nonAppIds->contains($p->id));

        // Detect role type for each path
        $pathRoleTypes = $paths->mapWithKeys(fn ($p) => [$p->id => $this->detectRoleType($p->name)]);

        return view('admin.resource-path-configuration.index', compact('paths', 'categorized', 'pathRoleTypes'));
    }

    /**
     * Detect the role type (delivery, sales, pre-sales) from a path name.
     */
    private function detectRoleType(string $name): string
    {
        $lower = strtolower($name);
        if (str_ends_with($lower, ': sales') || $name === 'Sales') {
            return 'sales';
        }
        if (str_ends_with($lower, ': pre-sales') || $name === 'Pre-Sales') {
            return 'pre-sales';
        }

        return 'delivery';
    }

    /**
     * Check if an application path belongs to a product group.
     */
    public function show(ResourcePath $path)
    {
        $path->load(['stages' => fn ($q) => $q->orderBy('stage_number'), 'gates', 'taskTemplates' => fn ($q) => $q->orderBy('sort_order')]);

        return view('admin.resource-path-configuration.show', compact('path'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $path = ResourcePath::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_default' => $request->boolean('is_default'),
            'created_by' => auth()->id(),
        ]);

        $defaultStages = [
            ['stage_number' => 1, 'name' => 'Registered', 'color' => '#6C757D', 'icon' => 'fal fa-id-card'],
            ['stage_number' => 2, 'name' => 'Trained', 'color' => '#0D6EFD', 'icon' => 'fal fa-graduation-cap'],
            ['stage_number' => 3, 'name' => 'Certified', 'color' => '#198754', 'icon' => 'fal fa-certificate'],
            ['stage_number' => 4, 'name' => 'Delivering', 'color' => '#FFC107', 'icon' => 'fal fa-project-diagram'],
            ['stage_number' => 5, 'name' => 'Expert', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
        ];

        foreach ($defaultStages as $i => $stage) {
            ResourcePathStage::create(array_merge($stage, [
                'resource_path_id' => $path->id,
                'sort_order' => $i,
            ]));
        }

        return redirect()->route('admin.resource-paths.show', $path)->with('success', 'Resource path created with default stages.');
    }

    public function update(Request $request, ResourcePath $path)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $path->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_default' => $request->boolean('is_default'),
        ]);

        return redirect()->back()->with('success', 'Resource path updated.');
    }

    public function addStage(Request $request, ResourcePath $path)
    {
        $request->validate([
            'stage_number' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
        ]);

        ResourcePathStage::create([
            'resource_path_id' => $path->id,
            'stage_number' => $request->stage_number,
            'name' => $request->name,
            'color' => $request->color ?? '#6C757D',
            'icon' => $request->icon,
            'sort_order' => $request->stage_number - 1,
        ]);

        return redirect()->back()->with('success', 'Stage added.');
    }

    public function updateStage(Request $request, ResourcePathStage $stage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $stage->update($request->only(['stage_number', 'name', 'color', 'icon', 'sort_order']));

        return redirect()->back()->with('success', 'Stage updated.');
    }

    public function destroyStage(ResourcePathStage $stage)
    {
        $stage->delete();

        return redirect()->back()->with('success', 'Stage removed.');
    }

    public function addGate(Request $request, ResourcePath $path)
    {
        $request->validate([
            'from_stage_id' => 'required|exists:resource_path_stages,id',
            'to_stage_id' => 'required|exists:resource_path_stages,id',
            'requirement_key' => 'required|string',
            'requirement_label' => 'required|string',
            'threshold_value' => 'required|numeric',
        ]);

        ResourcePathGate::create([
            'resource_path_id' => $path->id,
            'from_stage_id' => $request->from_stage_id,
            'to_stage_id' => $request->to_stage_id,
            'requirement_key' => $request->requirement_key,
            'requirement_label' => $request->requirement_label,
            'comparison' => $request->comparison ?? 'gte',
            'threshold_value' => $request->threshold_value,
            'unit' => $request->unit ?? 'count',
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Gate requirement added.');
    }

    public function updateGate(Request $request, ResourcePathGate $gate)
    {
        $request->validate([
            'requirement_label' => 'required|string',
            'threshold_value' => 'required|numeric',
        ]);

        $gate->update($request->only([
            'requirement_key', 'requirement_label', 'comparison',
            'threshold_value', 'unit', 'description',
        ]));

        return redirect()->back()->with('success', 'Gate updated.');
    }

    public function destroyGate(ResourcePathGate $gate)
    {
        $gate->delete();

        return redirect()->back()->with('success', 'Gate requirement removed.');
    }

    public function taskTemplates(Request $request)
    {
        $query = ResourceTaskTemplate::with(['path', 'stage']);

        if ($request->filled('path_id')) {
            $query->where('resource_path_id', $request->path_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('requirement_level')) {
            $query->where('requirement_level', $request->requirement_level);
        }

        $templates = $query->orderBy('sort_order')->get();
        $paths = ResourcePath::select('id', 'name')->orderBy('name')->get();

        return view('admin.resource-task-templates.index', compact('templates', 'paths'));
    }

    public function storeTaskTemplate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'requirement_level' => 'required|in:required,recommended,optional',
        ]);

        ResourceTaskTemplate::create([
            'resource_path_id' => $request->resource_path_id,
            'stage_id' => $request->stage_id,
            'category' => $request->category,
            'requirement_level' => $request->requirement_level,
            'title' => $request->title,
            'description' => $request->description,
            'completion_criteria' => $request->completion_criteria,
            'effort_size' => $request->effort_size ?? 'medium',
            'default_owner' => $request->default_owner ?? 'psm',
            'due_days_offset' => $request->due_days_offset,
            'auto_verify_rule' => $request->auto_verify_rule,
            'evidence_type' => $request->evidence_type ?? 'psm_attestation',
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Resource task template added.');
    }

    public function updateTaskTemplate(Request $request, ResourceTaskTemplate $template)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $template->update($request->only([
            'title', 'description', 'category', 'requirement_level',
            'completion_criteria', 'effort_size', 'default_owner',
            'due_days_offset', 'auto_verify_rule', 'evidence_type',
            'stage_id', 'is_active', 'sort_order',
        ]));

        return redirect()->back()->with('success', 'Resource task template updated.');
    }

    public function destroyTaskTemplate(ResourceTaskTemplate $template)
    {
        $template->delete();

        return redirect()->back()->with('success', 'Resource task template removed.');
    }
}
