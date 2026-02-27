<?php

namespace App\Console\Commands;

use App\Enums\DocumentStatus;
use App\Jobs\IngestDocumentJob;
use App\Models\KnowledgeDocument;
use Illuminate\Console\Command;

class RetryFailedIngestions extends Command
{
    protected $signature = 'kb:retry-failed
                            {--limit=0 : Maximum number of documents to retry (0 = all)}';

    protected $description = 'Re-queue failed knowledge base documents for ingestion';

    public function handle(): int
    {
        $query = KnowledgeDocument::where('status', DocumentStatus::Failed);

        $total = $query->count();

        if ($total === 0) {
            $this->info('No failed documents to retry.');

            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $count = 0;

        $query->each(function (KnowledgeDocument $document) use (&$count) {
            $document->update([
                'status' => DocumentStatus::Pending,
                'error_message' => null,
            ]);

            IngestDocumentJob::dispatch($document);
            $count++;
        });

        $this->info("{$count} of {$total} failed document(s) queued for re-ingestion.");

        return self::SUCCESS;
    }
}
