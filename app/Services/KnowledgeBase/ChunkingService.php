<?php

namespace App\Services\KnowledgeBase;

class ChunkingService
{
    protected int $chunkSize;

    protected int $overlapSize;

    public function __construct()
    {
        $this->chunkSize = config('knowledge_base.chunking.chunk_size', 750);
        $this->overlapSize = config('knowledge_base.chunking.overlap_size', 100);
    }

    /**
     * Split text into overlapping word-based chunks.
     *
     * @return list<array{text: string, word_count: int}>
     */
    public function chunk(string $text): array
    {
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return [];
        }

        $chunks = [];
        $step = max(1, $this->chunkSize - $this->overlapSize);

        for ($i = 0; $i < count($words); $i += $step) {
            $slice = array_slice($words, $i, $this->chunkSize);
            $chunkText = implode(' ', $slice);

            $chunks[] = [
                'text' => $chunkText,
                'word_count' => count($slice),
            ];

            if ($i + $this->chunkSize >= count($words)) {
                break;
            }
        }

        return $chunks;
    }
}
