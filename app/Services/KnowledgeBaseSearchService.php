<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use Illuminate\Support\Collection;

class KnowledgeBaseSearchService
{
    /**
     * Search the knowledge base using vector similarity.
     *
     * @return Collection<int, KnowledgeChunk>
     */
    public function search(string $query, ?int $limit = null, ?float $minSimilarity = null): Collection
    {
        $limit ??= config('knowledge_base.search.default_limit', 10);
        $minSimilarity ??= config('knowledge_base.search.min_similarity', 0.5);

        return KnowledgeChunk::query()
            ->whereVectorSimilarTo('embedding', $query, minSimilarity: $minSimilarity)
            ->with('document:id,title,file_name')
            ->limit($limit)
            ->get();
    }

    /**
     * Format search results as labelled context for system prompt injection.
     *
     * @param  Collection<int, KnowledgeChunk>  $chunks
     */
    public function formatAsContext(Collection $chunks): string
    {
        if ($chunks->isEmpty()) {
            return '';
        }

        $lines = [];

        foreach ($chunks as $index => $chunk) {
            $sourceLabel = $chunk->document?->title ?? $chunk->document?->file_name ?? 'Unknown';
            $lines[] = '[Source '.($index + 1).": {$sourceLabel}]";
            $lines[] = $chunk->content;
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Format search results as tool output text.
     *
     * @param  Collection<int, KnowledgeChunk>  $chunks
     */
    public function formatAsToolOutput(Collection $chunks): string
    {
        if ($chunks->isEmpty()) {
            return 'No relevant knowledge base documents found for this query.';
        }

        $lines = ["Found {$chunks->count()} relevant excerpts:"];

        foreach ($chunks as $chunk) {
            $lines[] = '';
            $lines[] = "--- [{$chunk->document?->title}] ({$chunk->document?->file_name}) ---";
            $lines[] = $chunk->content;
        }

        return implode("\n", $lines);
    }
}
