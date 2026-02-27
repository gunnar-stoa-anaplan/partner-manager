<?php

namespace App\Services\KnowledgeBase;

use App\Enums\DocumentSource;
use App\Enums\DocumentStatus;
use App\Jobs\IngestDocumentJob;
use App\Models\KnowledgeDocument;
use Illuminate\Support\Facades\Storage;

class StorageDiskScanner
{
    public function __construct(
        protected MimeTypeResolver $mimeTypeResolver,
    ) {}

    /**
     * Scan a Storage disk for supported documents and queue them for ingestion.
     *
     * @return array{queued: int, skipped: int}
     */
    public function scan(
        string $disk,
        string $prefix = '',
        bool $force = false,
        ?int $uploadedBy = null,
    ): array {
        $supportedExtensions = config('knowledge_base.supported_extensions', []);
        $storage = Storage::disk($disk);
        $files = $storage->allFiles($prefix);

        $queued = 0;
        $skipped = 0;

        foreach ($files as $relativePath) {
            $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

            if (! in_array($ext, $supportedExtensions)) {
                continue;
            }

            $mimeType = $this->mimeTypeResolver->fromExtension($ext);

            if ($mimeType === null) {
                continue;
            }

            // Download to temp for checksum computation
            $tempPath = $this->downloadToTemp($disk, $relativePath);

            try {
                $checksum = hash_file('sha256', $tempPath);
                $fileSize = filesize($tempPath);

                if (! $force && KnowledgeDocument::where('checksum', $checksum)->exists()) {
                    $skipped++;

                    continue;
                }

                $document = KnowledgeDocument::create([
                    'title' => pathinfo($relativePath, PATHINFO_FILENAME),
                    'file_name' => basename($relativePath),
                    'file_path' => $relativePath,
                    'mime_type' => $mimeType,
                    'file_size_bytes' => $fileSize,
                    'source' => DocumentSource::StorageDisk,
                    'storage_disk' => $disk,
                    'status' => DocumentStatus::Pending,
                    'checksum' => $checksum,
                    'uploaded_by' => $uploadedBy,
                ]);

                IngestDocumentJob::dispatch($document);
                $queued++;
            } finally {
                @unlink($tempPath);
            }
        }

        return ['queued' => $queued, 'skipped' => $skipped];
    }

    /**
     * Download a file from a Storage disk to a local temp path.
     */
    public function downloadToTemp(string $disk, string $relativePath): string
    {
        $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
        $tempPath = tempnam(sys_get_temp_dir(), 'kb_').".{$ext}";

        $stream = Storage::disk($disk)->readStream($relativePath);

        if ($stream === null) {
            throw new \RuntimeException("Cannot read file from disk [{$disk}]: {$relativePath}");
        }

        $localHandle = fopen($tempPath, 'wb');
        stream_copy_to_stream($stream, $localHandle);
        fclose($localHandle);
        fclose($stream);

        return $tempPath;
    }
}
