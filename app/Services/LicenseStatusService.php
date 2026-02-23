<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LicenseStatusService
{
    /**
     * Read license status from the shared volume written by the agent.
     *
     * @return array{status: string, tier: string, features: string[], warnings: string[], expires_at: string, checked_at: string, is_current: bool}|null
     */
    public function getLicenseStatus(): ?array
    {
        $statusFile = rtrim(config('version.agent_status_path'), '/').'/license-status.json';

        if (! file_exists($statusFile)) {
            return null;
        }

        try {
            $data = json_decode(file_get_contents($statusFile), true);
        } catch (\Throwable $e) {
            Log::warning('Failed to read license status file.', ['error' => $e->getMessage()]);

            return null;
        }

        if (! is_array($data) || ! isset($data['status'])) {
            return null;
        }

        // Consider stale if not checked in the last 5 minutes
        $checkedAt = isset($data['checked_at']) ? Carbon::parse($data['checked_at']) : null;
        $data['is_current'] = $checkedAt && $checkedAt->diffInMinutes(now()) < 5;

        return $data;
    }

    /**
     * Determine if the license has any active warnings that should be shown in the UI.
     *
     * @return array{level: string, message: string}[]
     */
    public function getWarningMessages(): array
    {
        $status = $this->getLicenseStatus();

        if (! $status || empty($status['warnings'])) {
            return [];
        }

        $messages = [];

        foreach ($status['warnings'] as $warning) {
            $messages[] = match ($warning) {
                'expired' => ['level' => 'danger', 'message' => 'Your license has expired. Please contact support to renew.'],
                'grace_period' => ['level' => 'warning', 'message' => 'Your license has expired and is in a grace period. Please renew soon.'],
                'expiring_soon' => ['level' => 'warning', 'message' => 'Your license expires in less than 30 days.'],
                'over_partner_limit' => ['level' => 'danger', 'message' => 'You have exceeded your licensed partner limit.'],
                'over_user_limit' => ['level' => 'danger', 'message' => 'You have exceeded your licensed user limit.'],
                'approaching_partner_limit' => ['level' => 'info', 'message' => 'You are approaching your licensed partner limit (80%+).'],
                'approaching_user_limit' => ['level' => 'info', 'message' => 'You are approaching your licensed user limit (80%+).'],
                default => ['level' => 'info', 'message' => "License warning: {$warning}"],
            };
        }

        return $messages;
    }
}
