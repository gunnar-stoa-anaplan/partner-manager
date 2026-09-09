<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Trigger one run of the Playwright automation microservice (automation/) and
 * record the outcome. Scheduled a few times a day from routes/console.php; the
 * scheduler's withoutOverlapping() plus the service's own in-flight guard keep
 * runs from stacking.
 */
class RunSiteAutomation extends Command
{
    protected $signature = 'automation:run {--timeout=120}';

    protected $description = 'Run the external-site Playwright automation once';

    public function handle(): int
    {
        $url = rtrim((string) config('services.playwright.url'), '/').'/run';

        try {
            $res = Http::timeout((int) $this->option('timeout'))
                ->acceptJson()
                ->post($url, ['triggered_at' => now()->toIso8601String()]);
        } catch (\Throwable $e) {
            $this->error('Automation service unreachable: '.$e->getMessage());
            Log::error('automation:run unreachable', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }

        $body = $res->json() ?? [];
        if ($res->successful() && ($body['ok'] ?? false)) {
            $this->info('Automation run ok in '.($body['ms'] ?? '?').'ms');
            Log::info('automation:run ok', $body);

            return self::SUCCESS;
        }

        $this->error('Automation run failed: '.($body['error'] ?? $res->status()));
        Log::error('automation:run failed', $body ?: ['status' => $res->status()]);

        return self::FAILURE;
    }
}
