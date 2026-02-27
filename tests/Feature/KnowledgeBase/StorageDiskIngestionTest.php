<?php

namespace Tests\Feature\KnowledgeBase;

use App\Enums\DocumentSource;
use App\Enums\DocumentStatus;
use App\Jobs\IngestDocumentJob;
use App\Models\KnowledgeDocument;
use App\Services\KnowledgeBase\StorageDiskScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageDiskIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Truncate PG table since RefreshDatabase only wraps the default MySQL connection
        KnowledgeDocument::query()->delete();
    }

    public function test_storage_disk_scan_creates_documents_and_dispatches_jobs(): void
    {
        Queue::fake();
        Storage::fake('test-disk');

        Storage::disk('test-disk')->put('guide.pdf', 'fake pdf content');
        Storage::disk('test-disk')->put('notes.txt', 'some notes');

        $scanner = app(StorageDiskScanner::class);
        $result = $scanner->scan('test-disk');

        $this->assertSame(2, $result['queued']);
        $this->assertSame(0, $result['skipped']);

        $doc = KnowledgeDocument::where('file_name', 'guide.pdf')->first();

        $this->assertNotNull($doc);
        $this->assertSame(DocumentSource::StorageDisk, $doc->source);
        $this->assertSame('test-disk', $doc->storage_disk);
        $this->assertSame('guide.pdf', $doc->file_path);
        $this->assertSame('application/pdf', $doc->mime_type);
        $this->assertSame(DocumentStatus::Pending, $doc->status);
        $this->assertTrue($doc->isRemote());

        Queue::assertPushed(IngestDocumentJob::class, 2);
    }

    public function test_storage_disk_scan_skips_duplicates_by_checksum(): void
    {
        Queue::fake();
        Storage::fake('test-disk');

        Storage::disk('test-disk')->put('guide.pdf', 'unique content for dedup test');

        $scanner = app(StorageDiskScanner::class);

        $first = $scanner->scan('test-disk');
        $this->assertSame(1, $first['queued']);

        $second = $scanner->scan('test-disk');
        $this->assertSame(0, $second['queued']);
        $this->assertSame(1, $second['skipped']);
    }

    public function test_storage_disk_scan_with_force_re_ingests_duplicates(): void
    {
        Queue::fake();
        Storage::fake('test-disk');

        Storage::disk('test-disk')->put('guide.pdf', 'content for force test');

        $scanner = app(StorageDiskScanner::class);

        $scanner->scan('test-disk');
        $result = $scanner->scan('test-disk', force: true);

        $this->assertSame(1, $result['queued']);
        $this->assertSame(0, $result['skipped']);
    }

    public function test_storage_disk_scan_filters_unsupported_extensions(): void
    {
        Queue::fake();
        Storage::fake('test-disk');

        Storage::disk('test-disk')->put('image.png', 'fake image');
        Storage::disk('test-disk')->put('archive.zip', 'fake archive');
        Storage::disk('test-disk')->put('readme.txt', 'valid text');

        $scanner = app(StorageDiskScanner::class);
        $result = $scanner->scan('test-disk');

        $this->assertSame(1, $result['queued']);
        $this->assertNull(KnowledgeDocument::where('file_name', 'image.png')->first());
        $this->assertNotNull(KnowledgeDocument::where('file_name', 'readme.txt')->first());
    }

    public function test_storage_disk_scan_respects_prefix(): void
    {
        Queue::fake();
        Storage::fake('test-disk');

        Storage::disk('test-disk')->put('docs/guide.pdf', 'pdf content');
        Storage::disk('test-disk')->put('other/notes.txt', 'notes');

        $scanner = app(StorageDiskScanner::class);
        $result = $scanner->scan('test-disk', prefix: 'docs');

        $this->assertSame(1, $result['queued']);

        $doc = KnowledgeDocument::where('file_name', 'guide.pdf')->first();

        $this->assertSame('docs/guide.pdf', $doc->file_path);
    }

    public function test_ingest_storage_disk_route_requires_authentication(): void
    {
        $response = $this->post('/knowledge-base/ingest/storage-disk', [
            'disk' => 'local',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_ingest_storage_disk_artisan_command(): void
    {
        Queue::fake();
        Storage::fake('test-disk');

        Storage::disk('test-disk')->put('report.pdf', 'pdf content for artisan');
        Storage::disk('test-disk')->put('data.csv', 'col1,col2');

        $this->artisan('knowledge:ingest-disk', ['disk' => 'test-disk'])
            ->assertSuccessful();

        $this->assertSame(2, KnowledgeDocument::where('storage_disk', 'test-disk')->count());
    }

    public function test_document_is_remote_returns_false_for_local_documents(): void
    {
        $doc = KnowledgeDocument::factory()->create();

        $this->assertFalse($doc->isRemote());
    }

    public function test_document_is_remote_returns_true_for_storage_disk_documents(): void
    {
        $doc = KnowledgeDocument::factory()->fromStorageDisk()->create();

        $this->assertTrue($doc->isRemote());
    }
}
