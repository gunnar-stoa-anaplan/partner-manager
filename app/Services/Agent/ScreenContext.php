<?php

namespace App\Services\Agent;

/**
 * What the agent knows about the screen the user is looking at: which screen,
 * the active filters, and a snapshot of the screen's key facts. The facts are
 * always built from row-level-scoped queries, so the context can never carry
 * data outside the caller's visibility. Provider-agnostic: the SimulatedProvider
 * templatizes these facts today; a real LLM provider receives the same context.
 */
class ScreenContext
{
    /**
     * @param  string  $screen  screen key, e.g. "pipeline"
     * @param  array<string, mixed>  $filters  active filter selections
     * @param  array<string, mixed>  $facts  scoped metric snapshot (label => value)
     * @param  bool  $unrestricted  whether the actor sees all partners (affects phrasing)
     */
    public function __construct(
        public readonly string $screen,
        public readonly array $filters = [],
        public readonly array $facts = [],
        public readonly bool $unrestricted = false,
    ) {}

    public function scopeLabel(): string
    {
        return $this->unrestricted ? 'all partners' : 'your partners';
    }
}
