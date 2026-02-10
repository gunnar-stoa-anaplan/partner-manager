<?php

namespace App\Http\Controllers;

use App\Models\Blueprint;
use App\Models\BlueprintEnrollment;
use App\Models\BlueprintProgress;
use App\Models\PartnerBlueprintAssignment;
use App\Models\Contact;
use Illuminate\Http\Request;

class BlueprintAssignmentController extends Controller
{
    public function catalog()
    {
        $blueprints = Blueprint::published()
            ->withCount('outcomes')
            ->with('outcomes.contentItems')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $grouped = $blueprints->groupBy('category');

        return view('blueprints.catalog', compact('grouped', 'blueprints'));
    }

    public function blueprintDetail(Blueprint $blueprint)
    {
        $blueprint->load('outcomes.contentItems');

        $user = auth()->user();

        // Get this PSM's assignments for this blueprint
        $myAssignments = PartnerBlueprintAssignment::where('blueprint_id', $blueprint->id)
            ->where('assigned_by', $user->id)
            ->with(['partner', 'enrollments.contact'])
            ->get();

        // Get partners the PSM manages
        $myPartners = $user->isPsm()
            ? $user->psmPartners()->with('contacts')->get()
            : \App\Models\Partner::with('contacts')->get();

        return view('blueprints.show', compact('blueprint', 'myAssignments', 'myPartners'));
    }

    public function assign(Request $request, Blueprint $blueprint)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'role' => 'required|in:gtm,pre_sales,delivery',
        ]);

        $existing = PartnerBlueprintAssignment::where('partner_id', $validated['partner_id'])
            ->where('blueprint_id', $blueprint->id)
            ->where('role', $validated['role'])
            ->first();

        if ($existing) {
            return back()->with('error', 'This partner is already assigned to this blueprint for the selected role.');
        }

        PartnerBlueprintAssignment::create([
            'partner_id' => $validated['partner_id'],
            'blueprint_id' => $blueprint->id,
            'role' => $validated['role'],
            'assigned_by' => auth()->id(),
        ]);

        return back()->with('success', 'Partner assigned to blueprint successfully.');
    }

    public function removeAssignment(PartnerBlueprintAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Assignment removed successfully.');
    }

    public function partnerProgress(PartnerBlueprintAssignment $assignment)
    {
        $assignment->load([
            'partner',
            'blueprint.outcomes.contentItems',
            'enrollments.contact',
            'enrollments.progress',
            'assignedBy',
        ]);

        $applicableItems = $assignment->applicableContentItems();

        return view('blueprints.partner-progress', compact('assignment', 'applicableItems'));
    }

    public function enrollContact(Request $request, PartnerBlueprintAssignment $assignment)
    {
        $validated = $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $enrolled = 0;
        foreach ($validated['contact_ids'] as $contactId) {
            // Verify contact belongs to the assigned partner
            $contact = Contact::where('id', $contactId)
                ->where('partner_id', $assignment->partner_id)
                ->first();

            if (!$contact) continue;

            // Check if already enrolled
            $exists = BlueprintEnrollment::where('partner_blueprint_assignment_id', $assignment->id)
                ->where('contact_id', $contactId)
                ->exists();

            if ($exists) continue;

            BlueprintEnrollment::create([
                'partner_blueprint_assignment_id' => $assignment->id,
                'contact_id' => $contactId,
                'enrolled_by' => auth()->id(),
            ]);
            $enrolled++;
        }

        return back()->with('success', "{$enrolled} contact(s) enrolled successfully.");
    }

    public function removeEnrollment(BlueprintEnrollment $enrollment)
    {
        $enrollment->delete();

        return back()->with('success', 'Enrollment removed successfully.');
    }

    public function contactProgress(BlueprintEnrollment $enrollment)
    {
        $enrollment->load([
            'contact',
            'assignment.blueprint.outcomes.contentItems',
            'assignment.partner',
            'progress',
        ]);

        $role = $enrollment->assignment->role;
        $applicableItems = $enrollment->assignment->applicableContentItems();

        // Group applicable items by outcome
        $outcomes = $enrollment->assignment->blueprint->outcomes;
        $progressMap = $enrollment->progress->keyBy('blueprint_content_item_id');

        return view('blueprints.contact-progress', compact('enrollment', 'outcomes', 'role', 'applicableItems', 'progressMap'));
    }

    public function toggleProgress(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:blueprint_enrollments,id',
            'content_item_id' => 'required|exists:blueprint_content_items,id',
        ]);

        $enrollment = BlueprintEnrollment::findOrFail($validated['enrollment_id']);

        $progress = BlueprintProgress::firstOrNew([
            'blueprint_enrollment_id' => $enrollment->id,
            'blueprint_content_item_id' => $validated['content_item_id'],
        ]);

        if ($progress->completed) {
            $progress->completed = false;
            $progress->completed_at = null;
        } else {
            $progress->completed = true;
            $progress->completed_at = now();
        }

        $progress->save();

        // Update enrollment status
        $enrollment->updateStatus();

        return response()->json([
            'completed' => $progress->completed,
            'enrollment_status' => $enrollment->fresh()->status,
            'completion_percentage' => $enrollment->completionPercentage(),
        ]);
    }
}
