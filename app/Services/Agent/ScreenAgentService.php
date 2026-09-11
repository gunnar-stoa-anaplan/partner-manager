<?php

namespace App\Services\Agent;

use App\Models\Opportunity;
use App\Models\Partner;
use App\Support\VisibilityContext;
use Illuminate\Support\Facades\App;

/**
 * Builds a scoped ScreenContext for the current actor and delegates to the
 * configured AgentProvider. All facts come from row-level-scoped models
 * (Opportunity, Partner, …), so the agent is grounded only in data the caller
 * may see — the scoping work is what makes this safe.
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
        return match ($screen) {
            'pipeline' => $this->pipelineFacts(),
            default => $this->portfolioFacts(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function pipelineFacts(): array
    {
        $open = Opportunity::query()->whereNotIn('stage', ['Closed Won', 'Closed Lost']);

        $facts = [
            'open_pipeline_value' => (float) (clone $open)->sum('amount'),
            'open_opportunity_count' => (int) (clone $open)->count(),
            'weighted_pipeline_value' => (float) (clone $open)->get(['amount', 'probability'])
                ->sum(fn ($o) => (float) ($o->amount ?? 0) * (((float) ($o->probability ?? 0)) / 100)),
            'partner_count' => (int) Partner::query()->count(),
        ];

        $topStage = (clone $open)->selectRaw('stage, sum(amount) as total')
            ->groupBy('stage')->orderByDesc('total')->first();
        if ($topStage) {
            $facts['top_stage_label'] = $topStage->stage;
            $facts['top_stage_value'] = (float) $topStage->total;
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
}
