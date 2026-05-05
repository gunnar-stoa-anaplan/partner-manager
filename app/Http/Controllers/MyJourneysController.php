<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use App\Services\JourneyDatabase;
use Illuminate\View\View;

class MyJourneysController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $journeys = Journey::query()
            ->visibleTo($user)
            ->orderBy('name')
            ->get()
            ->map(function (Journey $journey) {
                $db = new JourneyDatabase($journey);
                $journey->participant_count = $db->participants()->count();

                return $journey;
            });

        return view('journeys.my', compact('journeys'));
    }
}
