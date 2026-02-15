<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\LaunchProgramFeedback;
use App\Models\LaunchProgramMilestoneCompletion;
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

        return view('portal.launch-programs.show', compact(
            'program', 'enrollment', 'milestones', 'participant', 'completedIds', 'contact'
        ));
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
