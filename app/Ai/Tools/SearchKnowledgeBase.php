<?php

namespace App\Ai\Tools;

use App\Models\KnowledgeChunk;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchKnowledgeBase implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the organizational knowledge base using semantic similarity. '
            .'Returns the most relevant document excerpts matching the query. '
            .'Use this to ground your responses in organizational knowledge, '
            .'reference materials, best practices, and documentation.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = (string) $request->string('query');
        $limit = $request['limit'] ?? config('knowledge_base.search.default_limit', 10);
        $minSimilarity = config('knowledge_base.search.min_similarity', 0.5);

        $results = KnowledgeChunk::query()
            ->whereVectorSimilarTo('embedding', $query, minSimilarity: $minSimilarity)
            ->with('document:id,title,file_name')
            ->limit($limit)
            ->get();

        if ($results->isEmpty()) {
            return 'No relevant knowledge base documents found for this query.';
        }

        $lines = ["Found {$results->count()} relevant excerpts:"];

        foreach ($results as $chunk) {
            $lines[] = '';
            $lines[] = "--- [{$chunk->document?->title}] ({$chunk->document?->file_name}) ---";
            $lines[] = $chunk->content;
        }

        return implode("\n", $lines);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->required()
                ->description('The search query to find relevant knowledge base content'),
            'limit' => $schema->integer()
                ->description('Maximum number of results to return (default: 10)'),
        ];
    }
}
