<?php

namespace App\Http\Controllers\KnowledgeBase;

use App\Enums\DocumentSource;
use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\KnowledgeBase\IngestDirectoryRequest;
use App\Http\Requests\KnowledgeBase\IngestStorageDiskRequest;
use App\Http\Requests\KnowledgeBase\UploadDocumentRequest;
use App\Jobs\IngestDocumentJob;
use App\Models\KnowledgeDocument;
use App\Services\KnowledgeBase\StorageDiskScanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IngestionController extends Controller
{
    public function create(): View
    {
        $disks = array_keys(config('filesystems.disks', []));

        return view('knowledge-base.ingest', compact('disks'));
    }

    public function upload(UploadDocumentRequest $request): RedirectResponse
    {
        $disk = config('knowledge_base.storage.disk');
        $basePath = config('knowledge_base.storage.path');
        $count = 0;

        foreach ($request->file('documents') as $file) {
            $storedPath = $file->store($basePath, $disk);
            $fullPath = Storage::disk($disk)->path($storedPath);

            $document = KnowledgeDocument::create([
                'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $fullPath,
                'mime_type' => $file->getMimeType(),
                'file_size_bytes' => $file->getSize(),
                'source' => DocumentSource::Upload,
                'status' => DocumentStatus::Pending,
                'checksum' => hash_file('sha256', $fullPath),
                'uploaded_by' => $request->user()?->id,
            ]);

            IngestDocumentJob::dispatch($document);
            $count++;
        }

        return redirect()
            ->route('knowledge_base.documents.index')
            ->with('success', "{$count} document(s) queued for ingestion.");
    }

    public function directory(IngestDirectoryRequest $request): RedirectResponse
    {
        $path = $request->validated('path');
        $supportedExtensions = config('knowledge_base.supported_extensions', []);
        $count = 0;

        if (! is_dir($path)) {
            return redirect()
                ->route('knowledge_base.ingest.create')
                ->withErrors(['path' => 'Directory does not exist.']);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

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

            // Skip if already ingested with same checksum
            if (KnowledgeDocument::where('checksum', $checksum)->exists()) {
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
                'uploaded_by' => $request->user()?->id,
            ]);

            IngestDocumentJob::dispatch($document);
            $count++;
        }

        return redirect()
            ->route('knowledge_base.documents.index')
            ->with('success', "{$count} document(s) queued for ingestion from directory.");
    }

    public function storageDisk(IngestStorageDiskRequest $request, StorageDiskScanner $scanner): RedirectResponse
    {
        $disk = $request->validated('disk');
        $prefix = $request->validated('prefix') ?? '';

        try {
            $result = $scanner->scan(
                disk: $disk,
                prefix: $prefix,
                uploadedBy: $request->user()?->id,
            );
        } catch (\Throwable $e) {
            return redirect()
                ->route('knowledge_base.ingest.create')
                ->withErrors(['disk' => "Failed to scan disk [{$disk}]: {$e->getMessage()}"]);
        }

        return redirect()
            ->route('knowledge_base.documents.index')
            ->with('success', "{$result['queued']} document(s) queued from disk [{$disk}]. {$result['skipped']} duplicate(s) skipped.");
    }
}
