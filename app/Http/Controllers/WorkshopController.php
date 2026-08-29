<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use App\Models\WorkshopSession;
use App\Models\WorkshopSessionParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    public function index(): View
    {
        $workshops = Workshop::withCount('sessions')
            ->with('creator:id,name')
            ->orderByDesc('updated_at')
            ->get();

        return view('workshops.index', compact('workshops'));
    }

    public function create(): View
    {
        return view('workshops.create');
    }

    public function store(StoreWorkshopRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        $workshop = Workshop::create($validated);

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Workshop created successfully.');
    }

    public function show(Workshop $workshop): View
    {
        $workshop->load(['sessions' => fn ($query) => $query->withCount('activeParticipants')->orderBy('start_date')]);

        return view('workshops.show', compact('workshop'));
    }

    public function edit(Workshop $workshop): View
    {
        return view('workshops.edit', compact('workshop'));
    }

    public function rollup(Workshop $workshop): View
    {
        $workshop->load([
            'sessions' => fn ($query) => $query->orderBy('start_date'),
            'sessions.participants' => fn ($query) => $query->notRemoved()->with('contact.partner:id,name'),
        ]);

        $sessionStats = $workshop->sessions->map(function (WorkshopSession $session) {
            $total = $session->participants->count();
            $completed = $session->participants->whereNotNull('completed_at')->count();

            return [
                'session' => $session,
                'total' => $total,
                'completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        });

        $partnerStats = $workshop->sessions
            ->flatMap(fn (WorkshopSession $session) => $session->participants)
            ->groupBy(fn (WorkshopSessionParticipant $participant) => $participant->contact->partner?->name ?? 'Unknown')
            ->map(function ($participants, $partnerName) {
                $total = $participants->count();
                $completed = $participants->whereNotNull('completed_at')->count();

                return [
                    'partner' => $partnerName,
                    'total' => $total,
                    'completed' => $completed,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalParticipants = $sessionStats->sum('total');
        $totalCompleted = $sessionStats->sum('completed');

        return view('workshops.rollup', compact('workshop', 'sessionStats', 'partnerStats', 'totalParticipants', 'totalCompleted'));
    }

    public function update(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $workshop->update($request->validated());

        return redirect()->route('workshops.show', $workshop)
            ->with('success', 'Workshop updated successfully.');
    }

    public function destroy(Workshop $workshop): RedirectResponse
    {
        $workshop->delete();

        return redirect()->route('workshops.index')
            ->with('success', 'Workshop deleted successfully.');
    }
}
