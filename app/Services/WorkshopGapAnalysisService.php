<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\Workshop;
use App\Models\WorkshopSessionParticipant;
use Illuminate\Support\Collection;

class WorkshopGapAnalysisService
{
    private const STALE_MONTHS = 6;

    /**
     * Partners with zero workshop participation, or none in the last six
     * months, as invite candidates for a session administrator planning who
     * to bring into the next session - threshold-based, following
     * AlertGenerationService::checkEngagementGap()'s style. Queries Partner
     * directly (keyed by id) rather than reusing
     * WorkshopAnalyticsService::partnerParticipation()'s output, which
     * groups by partner *name* and can't be joined back to a Partner
     * record for "which partners are missing entirely" exclusion logic.
     *
     * @return Collection<int, array{partner: Partner, participants: int, last_session_at: ?\Illuminate\Support\Carbon}>
     */
    public function invitationCandidates(?Workshop $workshop = null): Collection
    {
        $participation = WorkshopSessionParticipant::query()
            ->notRemoved()
            ->whereHas('session', fn ($query) => $query->where('status', '!=', 'archived')
                ->when($workshop, fn ($q) => $q->where('workshop_id', $workshop->id)))
            ->with(['contact:id,partner_id', 'session:id,start_date'])
            ->get();

        $byPartnerId = $participation->groupBy('contact.partner_id');
        $staleCutoff = now()->subMonths(self::STALE_MONTHS);

        return Partner::query()
            ->get(['id', 'name'])
            ->map(function (Partner $partner) use ($byPartnerId) {
                $rows = $byPartnerId->get($partner->id, collect());

                return [
                    'partner' => $partner,
                    'participants' => $rows->count(),
                    'last_session_at' => $rows->pluck('session.start_date')->filter()->max(),
                ];
            })
            ->filter(fn (array $row) => $row['participants'] === 0 || $row['last_session_at']?->lt($staleCutoff))
            ->sortBy('participants')
            ->values();
    }
}
