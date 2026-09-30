<?php

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(
    'queue:work --queue=lead-imports --stop-when-empty --tries=3 --timeout=300'
)->everyMinute()->withoutOverlapping();
