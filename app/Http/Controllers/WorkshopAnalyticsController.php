<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Services\WorkshopAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class WorkshopAnalyticsController extends Controller
{
    public function __construct(private WorkshopAnalyticsService $analytics) {}

    public function workshops(Request $request): View
    {
        $workshops = Workshop::orderBy('name')->get();
        $selected = $this->selectedWorkshop($request, $workshops);

        $data = $this->analytics->sessionAnalytics($selected);

        return view('workshops.analytics.workshops', array_merge($data, [
            'workshops' => $workshops,
            'selected' => $selected,
        ]));
    }

    public function sessions(Request $request): View
    {
        $workshops = Workshop::orderBy('name')->get();
        $selected = $this->selectedWorkshop($request, $workshops);

        $data = $this->analytics->sessionAnalytics($selected);

        return view('workshops.analytics.sessions', array_merge($data, [
            'workshops' => $workshops,
            'selected' => $selected,
        ]));
    }

    public function participation(Request $request): View
    {
        $workshops = Workshop::orderBy('name')->get();
        $selected = $this->selectedWorkshop($request, $workshops);

        $rows = $this->analytics->partnerParticipation($selected);

        return view('workshops.analytics.participation', [
            'workshops' => $workshops,
            'selected' => $selected,
            'rows' => $rows,
        ]);
    }

    private function selectedWorkshop(Request $request, Collection $workshops): ?Workshop
    {
        $workshopId = $request->query('workshop_id');

        return $workshopId ? $workshops->firstWhere('id', (int) $workshopId) : null;
    }
}
