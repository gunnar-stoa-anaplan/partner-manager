<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KnowledgeBase\SearchRequest;
use App\Models\KnowledgeChunk;
use Illuminate\Http\JsonResponse;

class KnowledgeSearchController extends Controller
{
    public function __invoke(SearchRequest $request): JsonResponse
    {
        $limit = $request->integer('limit', config('knowledge_base.search.default_limit', 10));
        $minSimilarity = config('knowledge_base.search.min_similarity', 0.5);

        $results = KnowledgeChunk::query()
            ->whereVectorSimilarTo('embedding', $request->validated('query'), minSimilarity: $minSimilarity)
            ->with('document:id,title,file_name')
            ->limit($limit)
            ->get()
            ->map(fn (KnowledgeChunk $chunk) => [
                'content' => $chunk->content,
                'document' => $chunk->document?->title,
                'file_name' => $chunk->document?->file_name,
                'word_count' => $chunk->word_count,
                'chunk_index' => $chunk->chunk_index,
            ]);

        return response()->json([
            'data' => $results,
            'meta' => [
                'query' => $request->validated('query'),
                'count' => $results->count(),
            ],
        ]);
    }
}
