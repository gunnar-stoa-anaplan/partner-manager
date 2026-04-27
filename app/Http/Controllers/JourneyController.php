<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use App\Services\JourneyDatabase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    /**
     * Index: all journeys visible to the logged-in user.
     * Admins see all; PSMs see only assigned journeys.
     */
    public function index(Request $request): \Illuminate\View\View
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

        return view('journeys.index', compact('journeys'));
    }

    /**
     * Detail dashboard for a single journey.
     */
    public function show(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $db = new JourneyDatabase($journey);

        $participants = $db->participants();
        $pendingActions = $db->pendingActions();
        $recentActivity = $db->agentLog(10);

        $totalParticipants = $participants->count();

        // Active = stage < 5 OR just anyone with data
        $activeCount = $participants->filter(fn ($p) => isset($p->current_stage) && $p->current_stage < 5)->count();

        // Stalled = >14 days in stage and stage < 5
        $stalledCount = $participants->filter(function ($p) {
            if (! isset($p->current_stage) || $p->current_stage >= 5) {
                return false;
            }

            $lastActive = isset($p->last_active_at) ? Carbon::parse($p->last_active_at) : null;

            return $lastActive && $lastActive->diffInDays(now()) > 14;
        })->count();

        $pendingCount = $pendingActions->count();

        // Stage pipeline: participants per stage
        $stages = collect(JourneyDatabase::STAGES)->map(function ($stage, $stageNum) use ($participants) {
            $count = $participants->filter(fn ($p) => isset($p->current_stage) && (int) $p->current_stage === $stageNum)->count();

            return array_merge($stage, ['num' => $stageNum, 'count' => $count]);
        });

        return view('journeys.show', compact(
            'journey',
            'totalParticipants',
            'activeCount',
            'stalledCount',
            'pendingCount',
            'stages',
            'recentActivity',
        ));
    }

    /**
     * Full participant list for a journey.
     */
    public function participants(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $db = new JourneyDatabase($journey);
        $participants = $db->participants()->map(function ($p) {
            $stageNum = (int) ($p->current_stage ?? 0);
            $stageDef = JourneyDatabase::STAGES[$stageNum] ?? null;
            $p->stage_name = $stageDef ? $stageDef['name'] : 'Unknown';
            $p->stage_tasks = $stageDef ? $stageDef['tasks'] : 0;

            // Days in current stage
            $lastActive = isset($p->last_active_at) ? Carbon::parse($p->last_active_at) : null;
            $p->days_in_stage = $lastActive ? $lastActive->diffInDays(now()) : null;
            $p->is_stalled = $stageNum < 5 && $p->days_in_stage !== null && $p->days_in_stage > 14;

            return $p;
        });

        return view('journeys.participants', compact('journey', 'participants'));
    }

    /**
     * Pending actions approval queue for a journey.
     */
    public function pending(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $db = new JourneyDatabase($journey);
        $pendingActions = $db->pendingActions();

        return view('journeys.pending', compact('journey', 'pendingActions'));
    }

    /**
     * Agent activity log for a journey.
     */
    public function activity(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $db = new JourneyDatabase($journey);

        // Paginate in-memory (SQLite, expected small dataset)
        $perPage = 25;
        $page = (int) request('page', 1);
        $allLog = $db->agentLog(500);
        $total = $allLog->count();
        $log = $allLog->forPage($page, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            items: $log,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: ['path' => request()->url(), 'query' => request()->query()],
        );

        return view('journeys.activity', compact('journey', 'paginator'));
    }

    /**
     * Drag/drop builder — owner attaches assets (and later, sub-journeys) to stages.
     */
    public function builder(Journey $journey): \Illuminate\View\View
    {
        $this->authorizeJourneyAccess($journey);

        $assets = \App\Models\Asset::query()
            ->active()
            ->availableTo($journey)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $items = $journey->stageItems()
            ->orderBy('stage_number')
            ->orderBy('position')
            ->get();

        $assetIndex = \App\Models\Asset::query()
            ->whereIn('id', $items->where('item_type', \App\Models\JourneyStageItem::ITEM_TYPE_ASSET)->pluck('item_id'))
            ->get()
            ->keyBy('id');

        $itemsByStage = collect(JourneyDatabase::STAGES)
            ->keys()
            ->mapWithKeys(fn ($stageNum) => [$stageNum => $items->where('stage_number', $stageNum)->map(function ($item) use ($assetIndex) {
                $asset = $item->item_type === \App\Models\JourneyStageItem::ITEM_TYPE_ASSET
                    ? $assetIndex->get($item->item_id)
                    : null;

                return [
                    'item_id' => $item->id,
                    'item_type' => $item->item_type,
                    'asset_id' => $asset?->id,
                    'name' => $asset?->name ?? '(missing)',
                    'type' => $asset?->type,
                    'type_label' => $asset?->typeLabel(),
                    'required' => $item->required,
                    'position' => $item->position,
                ];
            })->values()->all()]);

        $stages = collect(JourneyDatabase::STAGES)
            ->map(fn ($s, $num) => array_merge($s, ['num' => $num]))
            ->values();

        return view('journeys.builder', compact('journey', 'assets', 'stages', 'itemsByStage'));
    }

    /**
     * Attach an item (asset for v1) to a stage.
     */
    public function attachItem(\App\Http\Requests\AttachJourneyStageItemRequest $request, Journey $journey): \Illuminate\Http\JsonResponse
    {
        $this->authorizeJourneyAccess($journey);

        $data = $request->validated();
        $type = $data['item_type'] ?? \App\Models\JourneyStageItem::ITEM_TYPE_ASSET;

        if ($type === \App\Models\JourneyStageItem::ITEM_TYPE_ASSET) {
            $asset = \App\Models\Asset::query()
                ->active()
                ->availableTo($journey)
                ->findOrFail($data['item_id']);

            $type = \App\Models\JourneyStageItem::ITEM_TYPE_ASSET;
            $itemId = $asset->id;
            $name = $asset->name;
            $typeLabel = $asset->typeLabel();
            $assetType = $asset->type;
            $assetId = $asset->id;
        } else {
            abort(422, 'Sub-journey items not yet supported.');
        }

        $position = $journey->stageItems()
            ->where('stage_number', $data['stage_number'])
            ->max('position') ?? -1;

        $item = $journey->stageItems()->updateOrCreate(
            [
                'stage_number' => $data['stage_number'],
                'item_type' => $type,
                'item_id' => $itemId,
            ],
            [
                'position' => $position + 1,
                'required' => $data['required'] ?? false,
                'added_by' => auth()->id(),
            ]
        );

        return response()->json([
            'item_id' => $item->id,
            'item_type' => $type,
            'asset_id' => $assetId,
            'name' => $name,
            'type' => $assetType,
            'type_label' => $typeLabel,
            'required' => $item->required,
            'stage_number' => $item->stage_number,
            'position' => $item->position,
        ]);
    }

    /**
     * Detach an item from a stage.
     */
    public function detachItem(Journey $journey, \App\Models\JourneyStageItem $item): \Illuminate\Http\JsonResponse
    {
        $this->authorizeJourneyAccess($journey);

        if ($item->journey_id !== $journey->id) {
            abort(404);
        }

        $item->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Enforce that the current user can view this journey.
     * Admins can view all; PSMs only their assigned journeys.
     */
    private function authorizeJourneyAccess(Journey $journey): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        $assigned = $journey->assignments()->where('user_id', $user->id)->exists();

        if (! $assigned) {
            abort(403, 'You are not assigned to this journey.');
        }
    }
}
