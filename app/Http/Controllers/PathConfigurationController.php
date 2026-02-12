<?php
namespace App\Http\Controllers;

use App\Models\PartnerPath;
use App\Models\PartnerPathStage;
use App\Models\PartnerPathGate;
use App\Models\TaskTemplate;
use Illuminate\Http\Request;

class PathConfigurationController extends Controller
{
    public function index()
    {
        $paths = PartnerPath::with(['stages', 'gates'])->get();
        return view('admin.path-configuration.index', compact('paths'));
    }

    public function show(PartnerPath $path)
    {
        $path->load(['stages' => fn($q) => $q->orderBy('stage_number'), 'gates', 'taskTemplates' => fn($q) => $q->orderBy('sort_order')]);
        return view('admin.path-configuration.show', compact('path'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $path = PartnerPath::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_default' => $request->boolean('is_default'),
            'created_by' => auth()->id(),
        ]);

        // Create default 5 stages
        $defaultStages = [
            ['stage_number' => 1, 'name' => 'Onboarded', 'color' => '#6C757D', 'icon' => 'fal fa-flag'],
            ['stage_number' => 2, 'name' => 'Enabled', 'color' => '#0D6EFD', 'icon' => 'fal fa-graduation-cap'],
            ['stage_number' => 3, 'name' => 'Performing', 'color' => '#198754', 'icon' => 'fal fa-chart-line'],
            ['stage_number' => 4, 'name' => 'Accelerating', 'color' => '#FFC107', 'icon' => 'fal fa-rocket'],
            ['stage_number' => 5, 'name' => 'Strategic', 'color' => '#DC3545', 'icon' => 'fal fa-crown'],
        ];

        foreach ($defaultStages as $i => $stage) {
            PartnerPathStage::create(array_merge($stage, [
                'partner_path_id' => $path->id,
                'sort_order' => $i,
            ]));
        }

        return redirect()->route('admin.paths.show', $path)->with('success', 'Path created with default stages.');
    }

    public function update(Request $request, PartnerPath $path)
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

        return redirect()->back()->with('success', 'Path updated.');
    }

    public function addStage(Request $request, PartnerPath $path)
    {
        $request->validate([
            'stage_number' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
        ]);

        PartnerPathStage::create([
            'partner_path_id' => $path->id,
            'stage_number' => $request->stage_number,
            'name' => $request->name,
            'color' => $request->color ?? '#6C757D',
            'icon' => $request->icon,
            'sort_order' => $request->stage_number - 1,
        ]);

        return redirect()->back()->with('success', 'Stage added.');
    }

    public function updateStage(Request $request, PartnerPathStage $stage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $stage->update($request->only(['stage_number', 'name', 'color', 'icon', 'sort_order']));

        return redirect()->back()->with('success', 'Stage updated.');
    }

    public function destroyStage(PartnerPathStage $stage)
    {
        $stage->delete();
        return redirect()->back()->with('success', 'Stage removed.');
    }

    public function addGate(Request $request, PartnerPath $path)
    {
        $request->validate([
            'from_stage_id' => 'required|exists:partner_path_stages,id',
            'to_stage_id' => 'required|exists:partner_path_stages,id',
            'requirement_key' => 'required|string',
            'requirement_label' => 'required|string',
            'threshold_value' => 'required|numeric',
        ]);

        PartnerPathGate::create([
            'partner_path_id' => $path->id,
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

    public function updateGate(Request $request, PartnerPathGate $gate)
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

    public function destroyGate(PartnerPathGate $gate)
    {
        $gate->delete();
        return redirect()->back()->with('success', 'Gate requirement removed.');
    }

    public function taskTemplates(Request $request)
    {
        $query = TaskTemplate::with(['path', 'stage']);

        if ($request->filled('path_id')) {
            $query->where('partner_path_id', $request->path_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('requirement_level')) {
            $query->where('requirement_level', $request->requirement_level);
        }

        $templates = $query->orderBy('sort_order')->get();
        $paths = PartnerPath::select('id', 'name')->orderBy('name')->get();

        return view('admin.task-templates.index', compact('templates', 'paths'));
    }

    public function storeTaskTemplate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'requirement_level' => 'required|in:required,recommended,optional',
        ]);

        TaskTemplate::create([
            'partner_path_id' => $request->partner_path_id,
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

        return redirect()->back()->with('success', 'Task template added.');
    }

    public function updateTaskTemplate(Request $request, TaskTemplate $template)
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

        return redirect()->back()->with('success', 'Task template updated.');
    }

    public function destroyTaskTemplate(TaskTemplate $template)
    {
        $template->delete();
        return redirect()->back()->with('success', 'Task template removed.');
    }
}
