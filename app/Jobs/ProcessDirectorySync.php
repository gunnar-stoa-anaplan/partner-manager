<?php

namespace App\Jobs;

use App\Models\IntegrationConnection;
use App\Models\IntegrationJob;
use App\Services\DirectorySyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDirectorySync implements ShouldQueue
{
    use Queueable;

    private int $syncConnectionId;

    private string $filePath;

    private int $syncJobId;

    public function __construct(
        IntegrationConnection $syncConnection,
        string $filePath,
        IntegrationJob $syncJob,
    ) {
        $this->syncConnectionId = $syncConnection->id;
        $this->filePath = $filePath;
        $this->syncJobId = $syncJob->id;
    }

    public function handle(DirectorySyncService $service): void
    {
        $syncConnection = IntegrationConnection::findOrFail($this->syncConnectionId);
        $syncJob = IntegrationJob::findOrFail($this->syncJobId);

        $service->syncFromCsv($syncConnection, $this->filePath, $syncJob);
    }

    public function failed(\Throwable $exception): void
    {
        IntegrationJob::where('id', $this->syncJobId)->update([
            'status' => 'failed',
            'error_log' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}
