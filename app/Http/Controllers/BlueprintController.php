<?php

namespace App\Http\Controllers;

use App\Models\Blueprint;
use App\Models\BlueprintContentItem;
use App\Models\BlueprintOutcome;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\BlueprintExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BlueprintController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $blueprints = Blueprint::withCount(['outcomes', 'assignments'])
            ->with('outcomes.contentItems')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('admin.blueprints.index', compact('blueprints', 'status'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.blueprints.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:application,product,delivery',
            'product_area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'status' => 'required|in:draft,submitted,published,archived',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = auth()->id();
        $this->applyStatusDates($validated, null);

        $blueprint = Blueprint::create($validated);

        return redirect()->route('blueprints.show', $blueprint)
            ->with('success', 'Blueprint created successfully.');
    }

    public function show(Blueprint $blueprint)
    {
        $blueprint->load(['outcomes.contentItems', 'assignments.partner', 'assignments.enrollments.contact', 'owner']);

        return view('admin.blueprints.show', compact('blueprint'));
    }

    public function edit(Blueprint $blueprint)
    {
        $blueprint->load('outcomes.contentItems');
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.blueprints.edit', compact('blueprint', 'users'));
    }

    public function update(Request $request, Blueprint $blueprint)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:application,product,delivery',
            'product_area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'status' => 'required|in:draft,submitted,published,archived',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $this->applyStatusDates($validated, $blueprint);
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

    // ─── Export ────────────────────────────────────────────────

    public function export(Blueprint $blueprint, BlueprintExportService $exportService): BinaryFileResponse
    {
        $blueprint->load(['outcomes.contentItems', 'assignments.partner', 'assignments.enrollments']);

        $tempPath = $exportService->export($blueprint);
        $filename = Str::slug($blueprint->name).'-blueprint.pptx';

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ])->deleteFileAfterSend();
    }

    // ─── Duplicate ──────────────────────────────────────────────

    public function duplicate(Blueprint $blueprint)
    {
        $blueprint->load('outcomes.contentItems');

        $copy = Blueprint::create([
            'name' => $blueprint->name.' - Copy',
            'category' => $blueprint->category,
            'product_area' => $blueprint->product_area,
            'description' => $blueprint->description,
            'version' => $blueprint->version,
            'status' => 'draft',
            'created_by' => auth()->id(),
            'owner_id' => null,
            'drafted_at' => now(),
        ]);

        foreach ($blueprint->outcomes as $outcome) {
            $newOutcome = BlueprintOutcome::create([
                'blueprint_id' => $copy->id,
                'title' => $outcome->title,
                'description' => $outcome->description,
                'sort_order' => $outcome->sort_order,
            ]);

            foreach ($outcome->contentItems as $item) {
                BlueprintContentItem::create([
                    'blueprint_outcome_id' => $newOutcome->id,
                    'sort_order' => $item->sort_order,
                    'title' => $item->title,
                    'url' => $item->url,
                    'duration_minutes' => $item->duration_minutes,
                    'duration_display' => $item->duration_display,
                    'source' => $item->source,
                    'prerequisites' => $item->prerequisites,
                    'gtm_applicable' => $item->gtm_applicable,
                    'pre_sales_applicable' => $item->pre_sales_applicable,
                    'delivery_applicable' => $item->delivery_applicable,
                ]);
            }
        }

        return redirect()->route('blueprints.show', $copy)
            ->with('success', 'Blueprint duplicated successfully.');
    }

    // ─── Submit for Publishing ──────────────────────────────────

    public function submit(Blueprint $blueprint)
    {
        if ($blueprint->owner_id !== auth()->id()) {
            abort(403, 'Only the blueprint owner can submit for publishing.');
        }

        $blueprint->update(['status' => 'submitted']);

        $this->notifyAdminsOfSubmission($blueprint);

        return redirect()->back()
            ->with('success', 'Blueprint submitted for publishing.');
    }

    // ─── Owner Edit Access ──────────────────────────────────────

    public function ownerEdit(Blueprint $blueprint)
    {
        if ($blueprint->owner_id !== auth()->id()) {
            abort(403);
        }

        $blueprint->load('outcomes.contentItems');
        $users = User::orderBy('name')->get(['id', 'name']);
        $ownerMode = true;

        return view('admin.blueprints.edit', compact('blueprint', 'users', 'ownerMode'));
    }

    public function ownerUpdate(Request $request, Blueprint $blueprint)
    {
        if ($blueprint->owner_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:application,product,delivery',
            'product_area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'status' => 'required|in:draft,submitted',
        ]);

        $this->applyStatusDates($validated, $blueprint);
        $blueprint->update($validated);

        return redirect()->route('blueprints.owner.edit', $blueprint)
            ->with('success', 'Blueprint updated successfully.');
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

    // ─── Private Helpers ────────────────────────────────────────

    /**
     * Auto-set date fields based on status transitions.
     *
     * @param  array<string, mixed>  $validated
     */
    private function applyStatusDates(array &$validated, ?Blueprint $existing): void
    {
        $newStatus = $validated['status'] ?? null;
        $oldStatus = $existing?->status;

        if ($newStatus && $newStatus !== $oldStatus) {
            match ($newStatus) {
                'published' => $validated['published_at'] = $validated['published_at'] ?? now(),
                'archived' => $validated['archived_at'] = $validated['archived_at'] ?? now(),
                'draft' => $validated['drafted_at'] = $validated['drafted_at'] ?? now(),
                default => null,
            };
        }
    }

    private function notifyAdminsOfSubmission(Blueprint $blueprint): void
    {
        $template = NotificationTemplate::where('event_type', 'blueprint_submitted')->first();

        if ($template) {
            $admins = User::whereRelation('role', 'slug', 'admin')->get();
            foreach ($admins as $admin) {
                $template->render([
                    'blueprint_name' => $blueprint->name,
                    'submitted_by' => $blueprint->owner->name ?? 'Unknown',
                    'recipient_name' => $admin->name,
                ]);
            }
        }

        \Illuminate\Support\Facades\Log::info('Blueprint submitted for publishing', [
            'blueprint_id' => $blueprint->id,
            'blueprint_name' => $blueprint->name,
            'submitted_by' => auth()->id(),
        ]);
    }
}
