<?php

namespace Tests\Unit;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeDocument;
use App\Services\KnowledgeBaseSearchService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class KnowledgeBaseSearchServiceTest extends TestCase
{
    private KnowledgeBaseSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KnowledgeBaseSearchService;
    }

    public function test_format_as_context_returns_empty_string_for_empty_collection(): void
    {
        $result = $this->service->formatAsContext(new Collection);

        $this->assertSame('', $result);
    }

    public function test_format_as_context_returns_labelled_sources(): void
    {
        $doc = new KnowledgeDocument;
        $doc->title = 'Revenue Planning Guide';
        $doc->file_name = 'revenue.pdf';

        $chunk = new KnowledgeChunk;
        $chunk->content = 'Revenue should be forecasted quarterly.';
        $chunk->setRelation('document', $doc);

        $result = $this->service->formatAsContext(new Collection([$chunk]));

        $this->assertStringContainsString('[Source 1: Revenue Planning Guide]', $result);
        $this->assertStringContainsString('Revenue should be forecasted quarterly.', $result);
    }

    public function test_format_as_context_numbers_multiple_sources(): void
    {
        $doc1 = new KnowledgeDocument;
        $doc1->title = 'First Doc';
        $doc1->file_name = 'first.pdf';

        $doc2 = new KnowledgeDocument;
        $doc2->title = 'Second Doc';
        $doc2->file_name = 'second.pdf';

        $chunk1 = new KnowledgeChunk;
        $chunk1->content = 'Content one.';
        $chunk1->setRelation('document', $doc1);

        $chunk2 = new KnowledgeChunk;
        $chunk2->content = 'Content two.';
        $chunk2->setRelation('document', $doc2);

        $result = $this->service->formatAsContext(new Collection([$chunk1, $chunk2]));

        $this->assertStringContainsString('[Source 1: First Doc]', $result);
        $this->assertStringContainsString('[Source 2: Second Doc]', $result);
    }

    public function test_format_as_tool_output_returns_empty_message_for_empty_collection(): void
    {
        $result = $this->service->formatAsToolOutput(new Collection);

        $this->assertSame('No relevant knowledge base documents found for this query.', $result);
    }

    public function test_format_as_tool_output_returns_formatted_excerpts(): void
    {
        $doc = new KnowledgeDocument;
        $doc->title = 'ALM Guide';
        $doc->file_name = 'alm.pdf';

        $chunk = new KnowledgeChunk;
        $chunk->content = 'ALM enables model versioning.';
        $chunk->setRelation('document', $doc);

        $result = $this->service->formatAsToolOutput(new Collection([$chunk]));

        $this->assertStringContainsString('Found 1 relevant excerpts:', $result);
        $this->assertStringContainsString('--- [ALM Guide] (alm.pdf) ---', $result);
        $this->assertStringContainsString('ALM enables model versioning.', $result);
    }

    public function test_format_as_context_falls_back_to_file_name_when_title_is_null(): void
    {
        $doc = new KnowledgeDocument;
        $doc->title = null;
        $doc->file_name = 'fallback.pdf';

        $chunk = new KnowledgeChunk;
        $chunk->content = 'Some content.';
        $chunk->setRelation('document', $doc);

        $result = $this->service->formatAsContext(new Collection([$chunk]));

        $this->assertStringContainsString('[Source 1: fallback.pdf]', $result);
    }
}
