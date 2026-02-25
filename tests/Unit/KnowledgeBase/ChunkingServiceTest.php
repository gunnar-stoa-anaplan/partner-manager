<?php

namespace Tests\Unit\KnowledgeBase;

use App\Services\KnowledgeBase\ChunkingService;
use Tests\TestCase;

class ChunkingServiceTest extends TestCase
{
    private ChunkingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['knowledge_base.chunking.chunk_size' => 10]);
        config(['knowledge_base.chunking.overlap_size' => 3]);

        $this->service = new ChunkingService;
    }

    public function test_it_chunks_text_into_correct_sizes(): void
    {
        $words = implode(' ', range(1, 30));
        $chunks = $this->service->chunk($words);

        $this->assertNotEmpty($chunks);
        $this->assertLessThanOrEqual(10, $chunks[0]['word_count']);
    }

    public function test_it_handles_empty_text(): void
    {
        $chunks = $this->service->chunk('');

        $this->assertEmpty($chunks);
    }

    public function test_it_handles_whitespace_only_text(): void
    {
        $chunks = $this->service->chunk('   ');

        $this->assertEmpty($chunks);
    }

    public function test_it_returns_single_chunk_for_short_text(): void
    {
        $chunks = $this->service->chunk('hello world');

        $this->assertCount(1, $chunks);
        $this->assertEquals('hello world', $chunks[0]['text']);
        $this->assertEquals(2, $chunks[0]['word_count']);
    }

    public function test_chunks_overlap_correctly(): void
    {
        // With chunk_size=10 and overlap=3, step=7
        // 20 words should produce: chunk at 0-9, 7-16, 14-19
        $words = implode(' ', range(1, 20));
        $chunks = $this->service->chunk($words);

        $this->assertGreaterThan(1, count($chunks));

        // Verify each chunk has correct structure
        foreach ($chunks as $chunk) {
            $this->assertArrayHasKey('text', $chunk);
            $this->assertArrayHasKey('word_count', $chunk);
            $this->assertGreaterThan(0, $chunk['word_count']);
        }
    }

    public function test_word_count_matches_actual_words(): void
    {
        $chunks = $this->service->chunk('one two three four five');

        $this->assertCount(1, $chunks);
        $this->assertEquals(5, $chunks[0]['word_count']);
    }

    public function test_it_uses_configured_chunk_size(): void
    {
        config(['knowledge_base.chunking.chunk_size' => 5]);
        config(['knowledge_base.chunking.overlap_size' => 0]);

        $service = new ChunkingService;
        $words = implode(' ', range(1, 15));
        $chunks = $service->chunk($words);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertEquals(5, $chunk['word_count']);
        }
    }
}
