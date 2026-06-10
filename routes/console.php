<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// SIM.4.2 — Check every minute for expired events: deactivate one-offs, restart recurring cycles.
Schedule::command('events:process')->everyMinute();
