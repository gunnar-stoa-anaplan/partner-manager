<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('anaplan:sync --skip-modules')->daily()->at('04:00');
Schedule::command('anaplan:sync --with-notifications')->weekly()->mondays()->at('06:00');
Schedule::command('workshops:release-expired-credentials')->daily()->at('05:00');
Schedule::command('workshops:send-reminders')->daily()->at('07:00');

// External-site Playwright automation (automation/). Adjust the times to your
// cadence; withoutOverlapping() stops a slow run from colliding with the next.
Schedule::command('automation:run')->cron('0 8,13,18 * * *')->withoutOverlapping();
