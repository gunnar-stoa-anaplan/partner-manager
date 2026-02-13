<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PartnerEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalCalendarController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $contact = Auth::guard('portal')->user();

        return view('portal.calendar.index', compact('contact'));
    }

    public function events(Request $request): JsonResponse
    {
        $contact = Auth::guard('portal')->user();
        $partner = $contact->partner;

        $start = Carbon::parse($request->query('start', now()->startOfMonth()));
        $end = Carbon::parse($request->query('end', now()->endOfMonth()));

        $events = PartnerEvent::where('partner_id', $partner->id)
            ->with('participants')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start) {
                        $q2->where('recurrence_type', '!=', 'none')
                            ->where('start_at', '<=', $start);
                    });
            })
            ->get();

        $calendarEvents = [];
        foreach ($events as $event) {
            $calendarEvents = array_merge($calendarEvents, $event->expandOccurrences($start, $end));
        }

        return response()->json($calendarEvents);
    }
}
