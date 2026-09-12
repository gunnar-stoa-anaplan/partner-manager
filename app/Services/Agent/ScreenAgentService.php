<?php

namespace App\Services\Agent;

use App\Models\Certification;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Support\VisibilityContext;
use Illuminate\Support\Facades\App;

/**
 * Builds a scoped ScreenContext for the current actor and delegates to the
 * configured AgentProvider. All facts come from row-level-scoped models
 * (Opportunity, Partner, Certification, …), so the agent is grounded only in
 * data the caller may see. Deterministic rankings (e.g. partners by
 * delivery-enablement gap) are computed here from scoped data — no external
 * LLM/egress needed to answer them.
 */
class ScreenAgentService
{
    public function __construct(private readonly VisibilityContext $visibility) {}

    public function provider(): AgentProvider
    {
        return match (config('agent.provider', 'simulated')) {
            // Future: 'anthropic' => App::make(AnthropicProvider::class),
            default => App::make(SimulatedProvider::class),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function contextFor(string $screen, array $filters = []): ScreenContext
    {
        return new ScreenContext(
            screen: $screen,
            filters: $filters,
            facts: $this->facts($screen),
            unrestricted: $this->visibility->visiblePartnerIds() === null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function facts(string $screen): array
    {
        // Every query below is subject to the ScopedToActor global scope, so the
        // figures are already limited to the actor's visible partners.
        $facts = match ($screen) {
            'pipeline' => $this->pipelineFacts(),
            default => $this->portfolioFacts(),
        };

        // A deterministic, scoped ranking available on every screen so questions
        // like "which partners need the most enablement for delivery" are
        // answered locally, no LLM required.
        $facts['delivery_enablement_gaps'] = $this->deliveryGapRanking();

        return $facts;
    }

    /**
     * @return array<string, mixed>
     */
    private function pipelineFacts(): array
    {
        $open = fn () => Opportunity::query()->whereNotIn('stage', ['Closed Won', 'Closed Lost']);

        $facts = [
            'open_pipeline_value' => (float) $open()->sum('amount'),
            'open_opportunity_count' => (int) $open()->count(),
            'weighted_pipeline_value' => (float) $open()->get(['amount', 'probability'])
                ->sum(fn ($o) => (float) ($o->amount ?? 0) * (((float) ($o->probability ?? 0)) / 100)),
            'partner_count' => (int) Partner::query()->count(),
            'stage_breakdown' => $this->stageBreakdown(),
        ];

        if ($facts['stage_breakdown'] !== []) {
            $top = collect($facts['stage_breakdown'])->sortByDesc('value')->first();
            $facts['top_stage_label'] = $top['stage'];
            $facts['top_stage_value'] = $top['value'];
        }

        return $facts;
    }

    /**
     * @return array<string, mixed>
     */
    private function portfolioFacts(): array
    {
        return [
            'partner_count' => (int) Partner::query()->count(),
            'open_opportunity_count' => (int) Opportunity::query()
                ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])->count(),
        ];
    }

    /**
     * Open pipeline value by stage (scoped).
     *
     * @return array<int, array{stage: string, value: float}>
     */
    private function stageBreakdown(): array
    {
        return Opportunity::query()
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->selectRaw('stage, sum(amount) as total')
            ->groupBy('stage')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['stage' => (string) $r->stage, 'value' => (float) $r->total])
            ->all();
    }

    /**
     * Partners ranked by delivery-enablement gap: open opportunities minus active
     * certifications (a scoped proxy for "needs the most enablement to deliver").
     * Both sides are row-level scoped, so the ranking never spans partners the
     * actor cannot see.
     *
     * @return array<int, array{partner: string, gap: int, open_opps: int, certified: int}>
     */
    private function deliveryGapRanking(int $limit = 10): array
    {
        $opps = Opportunity::query()->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->selectRaw('partner_id, count(*) as c')->groupBy('partner_id')->pluck('c', 'partner_id');

        $certs = Certification::query()->where('status', 'active')
            ->selectRaw('partner_id, count(*) as c')->groupBy('partner_id')->pluck('c', 'partner_id');

        $partnerIds = $opps->keys()->merge($certs->keys())->unique()->values();
        if ($partnerIds->isEmpty()) {
            return [];
        }

        $names = Partner::query()->whereIn('id', $partnerIds)->pluck('name', 'id');

        return $partnerIds
            ->map(fn ($pid) => [
                'partner' => (string) ($names[$pid] ?? "#{$pid}"),
                'open_opps' => (int) ($opps[$pid] ?? 0),
                'certified' => (int) ($certs[$pid] ?? 0),
                'gap' => (int) ($opps[$pid] ?? 0) - (int) ($certs[$pid] ?? 0),
            ])
            ->sortByDesc('gap')
            ->take($limit)
            ->values()
            ->all();
    }
}
