<?php

namespace App\Jobs;

use App\Models\KnowledgeDocument;
use App\Services\KnowledgeBase\DocumentIngestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IngestDocumentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(public KnowledgeDocument $document) {}

    public function handle(DocumentIngestionService $service): void
    {
        $service->ingest($this->document);
    }
}
