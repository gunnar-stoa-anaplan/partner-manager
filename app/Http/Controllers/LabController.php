<?php

namespace App\Http\Controllers;

use App\Models\AnaplanModel;
use App\Models\Contact;
use App\Models\EnablementSession;
use App\Models\Lab;
use App\Models\LabCheckpoint;
use App\Models\LabParticipant;
use App\Services\AnaplanApiService;
use App\Services\LabValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabController extends Controller
{
    public function index(): View
    {
        $labs = Lab::with(['session.event', 'goldenModel', 'creator', 'checkpoints', 'participants'])
            ->latest()
            ->get();

        return view('admin.labs.index', compact('labs'));
    }

    public function create(): View
    {
        $sessions = EnablementSession::with('event')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderByDesc('start_at')
            ->get();

        $models = AnaplanModel::where('active_state', '!=', 'ARCHIVED')
            ->orderBy('name')
            ->get();

        return view('admin.labs.create', compact('sessions', 'models'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enablement_session_id' => ['required', 'exists:enablement_sessions,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'golden_model_id' => ['required', 'exists:anaplan_models,id'],
            'tenant' => ['required', 'in:ifp,owp,spm'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        $lab = Lab::create($validated);

        return redirect()->route('labs.show', $lab)->with('success', 'Lab created successfully.');
    }

    public function show(Lab $lab): View
    {
        $lab->load([
            'session.event',
            'goldenModel.workspace',
            'checkpoints',
            'participants.contact',
            'participants.partner',
            'participants.assignedModel',
            'creator',
        ]);

        // Checkpoint analytics
        $checkpointStats = [];
        foreach ($lab->checkpoints as $checkpoint) {
            $checkpoint->loadCount('results');
            $checkpointStats[$checkpoint->id] = [
                'pass_rate' => $checkpoint->passRate(),
                'avg_attempts' => $checkpoint->averageAttempts(),
                'total_attempts' => $checkpoint->results_count,
            ];
        }

        // Available contacts for enrollment
        $enrolledContactIds = $lab->participants->pluck('contact_id')->toArray();
        $availableContacts = collect();

        if ($lab->session) {
            $sessionPartnerIds = $lab->session->registeredPartners->pluck('id')->toArray();
            if (! empty($sessionPartnerIds)) {
                $availableContacts = Contact::whereIn('partner_id', $sessionPartnerIds)
                    ->whereNotIn('id', $enrolledContactIds)
                    ->where('contact_type', 'resource')
                    ->orderBy('first_name')
                    ->get();
            }
        }

        // Available models for assignment
        $availableModels = AnaplanModel::where('active_state', '!=', 'ARCHIVED')
            ->orderBy('name')
            ->get();

        return view('admin.labs.show', compact('lab', 'checkpointStats', 'availableContacts', 'availableModels'));
    }

    public function edit(Lab $lab): View
    {
        $sessions = EnablementSession::with('event')
            ->orderByDesc('start_at')
            ->get();

        $models = AnaplanModel::where('active_state', '!=', 'ARCHIVED')
            ->orderBy('name')
            ->get();

        return view('admin.labs.edit', compact('lab', 'sessions', 'models'));
    }

    public function update(Request $request, Lab $lab): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        // Don't allow changing golden model after publish
        if ($lab->isDraft()) {
            $extraValidated = $request->validate([
                'enablement_session_id' => ['required', 'exists:enablement_sessions,id'],
                'golden_model_id' => ['required', 'exists:anaplan_models,id'],
                'tenant' => ['required', 'in:ifp,owp,spm'],
            ]);
            $validated = array_merge($validated, $extraValidated);
        }

        $lab->update($validated);

        return redirect()->route('labs.show', $lab)->with('success', 'Lab updated successfully.');
    }

    public function destroy(Lab $lab): RedirectResponse
    {
        if (! $lab->isDraft()) {
            return redirect()->route('labs.show', $lab)->with('error', 'Only draft labs can be deleted.');
        }

        $lab->delete();

        return redirect()->route('labs.index')->with('success', 'Lab deleted successfully.');
    }

    public function publish(Lab $lab, LabValidationService $validationService): RedirectResponse
    {
        if (! $lab->isDraft()) {
            return redirect()->route('labs.show', $lab)->with('error', 'Lab is already published.');
        }

        if ($lab->checkpoints()->count() === 0) {
            return redirect()->route('labs.show', $lab)->with('error', 'Add at least one checkpoint before publishing.');
        }

        $snapshot = $validationService->captureGoldenSnapshot($lab);

        $lab->update([
            'status' => 'published',
            'published_at' => now(),
            'golden_snapshot' => $snapshot,
        ]);

        return redirect()->route('labs.show', $lab)->with('success', 'Lab published. Golden model snapshot captured.');
    }

    public function addCheckpoint(Request $request, Lab $lab): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'checkpoint_type' => ['required', 'in:formula,structure,config,value,list'],
            'validation_config' => ['required', 'json'],
            'is_required' => ['boolean'],
            'points' => ['integer', 'min:1'],
            'hint' => ['nullable', 'string'],
        ]);

        $validated['validation_config'] = json_decode($validated['validation_config'], true);
        $validated['sort_order'] = $lab->checkpoints()->max('sort_order') + 1;
        $validated['lab_id'] = $lab->id;

        LabCheckpoint::create($validated);

        return redirect()->route('labs.show', $lab)->with('success', 'Checkpoint added.');
    }

    public function updateCheckpoint(Request $request, LabCheckpoint $checkpoint): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'checkpoint_type' => ['required', 'in:formula,structure,config,value,list'],
            'validation_config' => ['required', 'json'],
            'is_required' => ['boolean'],
            'points' => ['integer', 'min:1'],
            'hint' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $validated['validation_config'] = json_decode($validated['validation_config'], true);

        $checkpoint->update($validated);

        return redirect()->route('labs.show', $checkpoint->lab)->with('success', 'Checkpoint updated.');
    }

    public function destroyCheckpoint(LabCheckpoint $checkpoint): RedirectResponse
    {
        $lab = $checkpoint->lab;
        $checkpoint->delete();

        return redirect()->route('labs.show', $lab)->with('success', 'Checkpoint removed.');
    }

    public function browseModel(Lab $lab, AnaplanApiService $api): JsonResponse
    {
        $goldenModel = $lab->goldenModel;
        $workspace = $goldenModel->workspace;

        if (! $workspace) {
            return response()->json(['error' => 'Workspace not found'], 404);
        }

        $modules = $api->fetchModules($workspace->anaplan_id, $goldenModel->anaplan_id);

        return response()->json(['modules' => $modules ?? []]);
    }

    public function browseModule(Lab $lab, string $moduleId, AnaplanApiService $api): JsonResponse
    {
        $goldenModel = $lab->goldenModel;

        $lineItems = $api->fetchLineItems($goldenModel->anaplan_id, $moduleId, true);

        return response()->json(['lineItems' => $lineItems ?? []]);
    }

    public function browseLists(Lab $lab, AnaplanApiService $api): JsonResponse
    {
        $goldenModel = $lab->goldenModel;
        $workspace = $goldenModel->workspace;

        if (! $workspace) {
            return response()->json(['error' => 'Workspace not found'], 404);
        }

        $lists = $api->fetchLists($workspace->anaplan_id, $goldenModel->anaplan_id);

        return response()->json(['lists' => $lists ?? []]);
    }

    public function addParticipant(Request $request, Lab $lab): RedirectResponse
    {
        $validated = $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'assigned_model_id' => ['nullable', 'exists:anaplan_models,id'],
        ]);

        $contact = Contact::findOrFail($validated['contact_id']);

        if ($lab->participants()->where('contact_id', $contact->id)->exists()) {
            return redirect()->route('labs.show', $lab)->with('error', 'Contact is already enrolled in this lab.');
        }

        LabParticipant::create([
            'lab_id' => $lab->id,
            'contact_id' => $contact->id,
            'partner_id' => $contact->partner_id,
            'assigned_model_id' => $validated['assigned_model_id'] ?? null,
            'total_points_possible' => $lab->totalPoints(),
            'enrolled_by' => auth()->id(),
        ]);

        return redirect()->route('labs.show', $lab)->with('success', $contact->full_name.' enrolled in lab.');
    }

    public function removeParticipant(LabParticipant $participant): RedirectResponse
    {
        $lab = $participant->lab;
        $participant->delete();

        return redirect()->route('labs.show', $lab)->with('success', 'Participant removed.');
    }
}
