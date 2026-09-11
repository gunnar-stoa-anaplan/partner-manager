<?php

namespace App\Services\Agent;

/**
 * Strategy for turning a (scoped) ScreenContext into insights and answers.
 * Implementations must ground every statement in the context's facts — they
 * receive only already-scoped data, so they cannot leak. SimulatedProvider is
 * the default; a real LLM provider implements the same contract.
 */
interface AgentProvider
{
    /**
     * Proactive insights about the current screen (2–3 short statements).
     *
     * @return array<int, string>
     */
    public function insights(ScreenContext $context): array;

    /**
     * Answer a user's question about the current screen's data.
     */
    public function answer(ScreenContext $context, string $question): string;

    /** Identifier for the active provider (for UI/debug). */
    public function name(): string;
}
