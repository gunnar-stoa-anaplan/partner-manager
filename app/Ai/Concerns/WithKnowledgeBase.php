<?php

namespace App\Ai\Concerns;

use App\Services\KnowledgeBaseSearchService;
use Illuminate\Support\Collection;

trait WithKnowledgeBase
{
    /**
     * Pre-fetched knowledge base chunks.
     *
     * @var Collection<int, \App\Models\KnowledgeChunk>|null
     */
    protected ?Collection $prefetchedChunks = null;

    /**
     * Pre-fetch knowledge base content for the given query.
     */
    public function withPrefetchedContext(string $query): static
    {
        $this->prefetchedChunks = app(KnowledgeBaseSearchService::class)->search(
            query: $query,
            limit: config('knowledge_base.search.prefetch_limit', 5),
        );

        return $this;
    }

    /**
     * Build the knowledge base grounding section for inclusion in instructions.
     */
    protected function buildKnowledgeBaseSection(): string
    {
        if ($this->prefetchedChunks === null || $this->prefetchedChunks->isEmpty()) {
            return '';
        }

        $context = app(KnowledgeBaseSearchService::class)->formatAsContext($this->prefetchedChunks);

        return <<<SECTION

        ## Knowledge Base Context

        The following excerpts from the organizational knowledge base are directly relevant to
        this request. You MUST prefer this information over your general training data. When you
        use information from these sources, cite them by their source label (e.g., [Source 1]).

        {$context}
        SECTION;
    }

    /**
     * Get the citation and grounding instructions to append to agent instructions.
     */
    protected function citationInstructions(): string
    {
        return <<<'CITATION'

        ## Citation & Grounding Rules

        1. **Prefer knowledge base content.** When the knowledge base provides information relevant
           to your response, always prefer it over your general training data.
        2. **Cite your sources.** When referencing knowledge base content, include the source label
           in brackets, e.g., [Source 1: Document Title].
        3. **Acknowledge gaps.** If neither the pre-loaded context nor the search tool returns
           relevant information, say so explicitly rather than fabricating an answer.
        4. **Follow-up searches.** If the pre-loaded context does not fully cover the topic, use
           the SearchKnowledgeBase tool with more specific queries to find additional material.
        CITATION;
    }
}
