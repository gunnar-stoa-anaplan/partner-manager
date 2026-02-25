<?php

namespace App\Console\Commands;

use App\Enums\DocumentSource;
use App\Enums\DocumentStatus;
use App\Jobs\IngestDocumentJob;
use App\Models\KnowledgeDocument;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class IngestKnowledgeDirectory extends Command
{
    protected $signature = 'knowledge:ingest-directory {path} {--force : Re-ingest files even if checksum matches}';

    protected $description = 'Scan a directory for supported documents and queue them for ingestion';

    public function handle(): int
    {
        $path = $this->argument('path');
        $force = $this->option('force');
        $supportedExtensions = config('knowledge_base.supported_extensions', []);

        if (! is_dir($path)) {
            $this->error("Directory does not exist: {$path}");

            return self::FAILURE;
        }

        $this->info("Scanning {$path} for supported documents...");

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        $queued = 0;
        $skipped = 0;

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $ext = strtolower($file->getExtension());

            if (! in_array($ext, $supportedExtensions)) {
                continue;
            }

            $filePath = $file->getRealPath();
            $checksum = hash_file('sha256', $filePath);

            if (! $force && KnowledgeDocument::where('checksum', $checksum)->exists()) {
                $skipped++;

                continue;
            }

            $document = KnowledgeDocument::create([
                'title' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                'file_name' => $file->getFilename(),
                'file_path' => $filePath,
                'mime_type' => mime_content_type($filePath),
                'file_size_bytes' => $file->getSize(),
                'source' => DocumentSource::Directory,
                'status' => DocumentStatus::Pending,
                'checksum' => $checksum,
            ]);

            IngestDocumentJob::dispatch($document);
            $queued++;

            $this->line("  Queued: {$file->getFilename()}");
        }

        $this->info("Done. Queued {$queued} document(s) for ingestion. Skipped {$skipped} duplicate(s).");

        return self::SUCCESS;
    }
}
