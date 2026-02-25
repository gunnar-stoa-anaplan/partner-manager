<?php

namespace App\Services\KnowledgeBase;

use App\Enums\DocumentStatus;
use App\Models\KnowledgeChunk;
use App\Models\KnowledgeDocument;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;

class DocumentIngestionService
{
    public function __construct(
        protected DocumentParserFactory $parserFactory,
        protected ChunkingService $chunkingService,
        protected StorageDiskScanner $storageDiskScanner,
    ) {}

    /**
     * Ingest a single document: parse, chunk, embed, and store.
     */
    public function ingest(KnowledgeDocument $document): void
    {
        $document->update(['status' => DocumentStatus::Processing]);

        $tempPath = null;

        try {
            $filePath = $this->resolveFilePath($document, $tempPath);

            $parser = $this->parserFactory->make($document->mime_type);
            $text = $parser->parse($filePath);

            // Sanitize to valid UTF-8 — PDF parsers can emit malformed byte
            // sequences that cause json_encode failures in the embedding API.
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

            if (trim($text) === '') {
                throw new \RuntimeException('Document produced no extractable text.');
            }

            $chunks = $this->chunkingService->chunk($text);

            if (empty($chunks)) {
                throw new \RuntimeException('Document produced no chunks after splitting.');
            }

            // Delete existing chunks (supports re-ingestion)
            $document->chunks()->delete();

            $batchSize = config('knowledge_base.embedding.batch_size', 20);
            $chunkIndex = 0;

            foreach (array_chunk($chunks, $batchSize) as $batch) {
                $texts = array_column($batch, 'text');
                $response = Embeddings::for($texts)->generate();
                $embeddings = $response->embeddings;

                foreach ($batch as $i => $chunk) {
                    KnowledgeChunk::create([
                        'document_id' => $document->id,
                        'chunk_index' => $chunkIndex,
                        'content' => $chunk['text'],
                        'embedding' => $embeddings[$i],
                        'word_count' => $chunk['word_count'],
                    ]);

                    $chunkIndex++;
                }
            }

            $document->update([
                'status' => DocumentStatus::Completed,
                'chunk_count' => $chunkIndex,
                'ingested_at' => now(),
                'error_message' => null,
            ]);

            Log::info("Knowledge base: ingested {$chunkIndex} chunks from {$document->file_name}");
        } catch (\Throwable $e) {
            $document->update([
                'status' => DocumentStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Knowledge base: failed to ingest {$document->file_name}: {$e->getMessage()}");

            throw $e;
        } finally {
            if ($tempPath !== null) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Resolve a local filesystem path for the document.
     * For remote storage disk documents, download to a temp file.
     *
     * @param  string|null  &$tempPath  Set to the temp path if a download occurred (caller must clean up).
     */
    protected function resolveFilePath(KnowledgeDocument $document, ?string &$tempPath): string
    {
        if (! $document->isRemote()) {
            return $document->file_path;
        }

        $tempPath = $this->storageDiskScanner->downloadToTemp(
            $document->storage_disk,
            $document->file_path,
        );

        return $tempPath;
    }
}
