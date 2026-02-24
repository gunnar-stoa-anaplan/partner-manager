<?php

namespace App\Console\Commands;

use App\Services\KnowledgeBase\StorageDiskScanner;
use Illuminate\Console\Command;

class IngestStorageDisk extends Command
{
    protected $signature = 'knowledge:ingest-disk
        {disk : The Storage disk name to scan}
        {--prefix= : Only scan files under this prefix/folder}
        {--force : Re-ingest files even if checksum matches}';

    protected $description = 'Scan a Laravel Storage disk for supported documents and queue them for ingestion';

    public function handle(StorageDiskScanner $scanner): int
    {
        $disk = $this->argument('disk');
        $prefix = $this->option('prefix') ?? '';
        $force = $this->option('force');

        $this->info("Scanning disk [{$disk}] (prefix: '{$prefix}') for supported documents...");

        try {
            $result = $scanner->scan(
                disk: $disk,
                prefix: $prefix,
                force: $force,
            );
        } catch (\Throwable $e) {
            $this->error("Failed to scan disk [{$disk}]: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("Done. Queued {$result['queued']} document(s) for ingestion. Skipped {$result['skipped']} duplicate(s).");

        return self::SUCCESS;
    }
}
