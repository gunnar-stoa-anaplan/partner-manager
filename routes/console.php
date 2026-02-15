<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('anaplan:sync --skip-modules')->daily()->at('04:00');
Schedule::command('anaplan:sync --with-notifications')->weekly()->mondays()->at('06:00');
