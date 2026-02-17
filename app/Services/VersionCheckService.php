<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VersionCheckService
{
    /**
     * Get the current application version.
     */
    public function currentVersion(): string
    {
        return config('version.current') ?? '1.0.0';
    }

    /**
     * Check if update information is available from the agent.
     *
     * @return array{version: string, type: string, released: string, summary: string, notes_url: string, changelog: array<int, string>}|null
     */
    public function getAvailableUpdate(): ?array
    {
        $statusFile = $this->statusFilePath('version-check.json');

        if (! file_exists($statusFile)) {
            return null;
        }

        $data = json_decode(file_get_contents($statusFile), true);

        if (! is_array($data) || ! isset($data['version'])) {
            return null;
        }

        if (version_compare($data['version'], $this->currentVersion(), '<=')) {
            return null;
        }

        return $data;
    }

    /**
     * Check if an update has already been accepted and is pending deployment.
     */
    public function isUpdateAccepted(): bool
    {
        return file_exists($this->statusFilePath('update-accepted.json'));
    }

    /**
     * Accept an available update by writing the acceptance file for the agent.
     */
    public function acceptUpdate(string $version, int $acceptedByUserId): bool
    {
        $statusPath = config('version.agent_status_path');

        if (! is_dir($statusPath)) {
            Log::warning('Agent status path does not exist, cannot accept update.', [
                'path' => $statusPath,
            ]);

            return false;
        }

        $payload = [
            'version' => $version,
            'accepted_at' => now()->toIso8601String(),
            'accepted_by_user_id' => $acceptedByUserId,
        ];

        $written = file_put_contents(
            $this->statusFilePath('update-accepted.json'),
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $written !== false;
    }

    /**
     * Resolve the full path to a file in the agent status directory.
     */
    private function statusFilePath(string $filename): string
    {
        return rtrim(config('version.agent_status_path'), '/').'/'.$filename;
    }
}
