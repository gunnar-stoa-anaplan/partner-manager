<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\LaunchProgramFeedback;
use App\Models\LaunchProgramMilestoneCompletion;
use App\Models\LaunchProgramParticipant;
use App\Models\LaunchProgramPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalLaunchProgramController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $enrollments = LaunchProgramPartner::where('partner_id', $partner->id)
            ->whereNotIn('status', ['withdrawn'])
            ->with([
                'program.product',
                'program.milestones',
                'participants.milestoneCompletions',
            ])
            ->get();

        return view('portal.launch-programs.index', compact('enrollments'));
    }

    public function show(LaunchProgramPartner $programPartner): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();

        if ($programPartner->partner_id !== $contact->partner_id) {
            abort(403);
        }

        $programPartner->load([
            'program.product',
            'program.milestones',
            'participants.contact',
            'participants.milestoneCompletions',
            'feedback',
        ]);

        $program = $programPartner->program;
        $milestones = $program->milestones;
        $enrollment = $programPartner;

        // Find the current contact's participant record
        $participant = $enrollment->participants->firstWhere('contact_id', $contact->id);
        $completedIds = $participant
            ? $participant->milestoneCompletions->pluck('launch_program_milestone_id')->toArray()
            : [];

        // Available contacts for "Add Team Member" (same partner, not already participants)
        $existingContactIds = $enrollment->participants->pluck('contact_id');
        $availableContacts = Contact::where('partner_id', $contact->partner_id)
            ->whereNotIn('id', $existingContactIds)
            ->orderBy('first_name')
            ->get();

        // Risk data
        $allCompletedMilestoneIds = $enrollment->participants
            ->flatMap(fn ($p) => $p->milestoneCompletions->pluck('launch_program_milestone_id'))
            ->unique();
        $incompleteMilestones = $milestones->reject(fn ($ms) => $allCompletedMilestoneIds->contains($ms->id))->values();
        $completionPct = $enrollment->completionPercentage();
        $daysToGa = $program->target_ga_date ? (int) now()->diffInDays($program->target_ga_date, false) : null;

        return view('portal.launch-programs.show', compact(
            'program', 'enrollment', 'milestones', 'participant', 'completedIds', 'contact',
            'availableContacts', 'incompleteMilestones', 'completionPct', 'daysToGa'
        ));
    }

    public function addParticipant(Request $request, LaunchProgramPartner $programPartner): \Illuminate\Http\RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        if ($programPartner->partner_id !== $contact->partner_id) {
            abort(403);
        }

        $validated = $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgramParticipant::ROLES))],
        ]);

        // Verify the selected contact belongs to the same partner
        $targetContact = Contact::where('id', $validated['contact_id'])
            ->where('partner_id', $contact->partner_id)
            ->firstOrFail();

        // Prevent duplicates
        if ($programPartner->participants()->where('contact_id', $targetContact->id)->exists()) {
            return back()->with('error', $targetContact->full_name.' is already on the team.');
        }

        $programPartner->participants()->create([
            'contact_id' => $targetContact->id,
            'role' => $validated['role'],
            'assigned_by' => null,
        ]);

        return back()->with('success', $targetContact->full_name.' added to the team.');
    }

    public function removeParticipant(LaunchProgramPartner $programPartner, LaunchProgramParticipant $participant): \Illuminate\Http\RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        if ($programPartner->partner_id !== $contact->partner_id) {
            abort(403);
        }

        if ($participant->launch_program_partner_id !== $programPartner->id) {
            abort(403);
        }

        // Prevent removing yourself
        if ($participant->contact_id === $contact->id) {
            return back()->with('error', 'You cannot remove yourself from the team.');
        }

        $name = $participant->contact->full_name;
        $participant->delete();

        return back()->with('success', $name.' removed from the team.');
    }

    public function completeMilestone(Request $request, LaunchProgramPartner $programPartner, int $milestoneId): \Illuminate\Http\RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        if ($programPartner->partner_id !== $contact->partner_id) {
            abort(403);
        }

        $participant = $programPartner->participants()->where('contact_id', $contact->id)->first();

        if (! $participant) {
            return back()->with('error', 'You are not a participant in this program.');
        }

        $exists = LaunchProgramMilestoneCompletion::where('launch_program_participant_id', $participant->id)
            ->where('launch_program_milestone_id', $milestoneId)
            ->exists();

        if (! $exists) {
            LaunchProgramMilestoneCompletion::create([
                'launch_program_participant_id' => $participant->id,
                'launch_program_milestone_id' => $milestoneId,
                'completed_at' => now(),
            ]);
        }

        return back()->with('success', 'Milestone completed!');
    }

    public function submitFeedback(Request $request, LaunchProgramPartner $programPartner): \Illuminate\Http\RedirectResponse
    {
        $contact = Auth::guard('portal')->user();

        if ($programPartner->partner_id !== $contact->partner_id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(LaunchProgramFeedback::TYPES))],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'body' => ['required', 'string'],
        ]);

        LaunchProgramFeedback::create([
            'launch_program_partner_id' => $programPartner->id,
            'contact_id' => $contact->id,
            'type' => $validated['type'],
            'rating' => $validated['rating'],
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Feedback submitted. Thank you!');
    }
}
