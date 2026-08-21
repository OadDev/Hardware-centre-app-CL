<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Only takes effect on a host where `php artisan schedule:run` is actually
// cron'd — the admin Abandoned Carts page runs the same sweep on every
// load regardless, so this isn't required for the feature to work.
Schedule::command('cart:sweep-abandoned')->everyFiveMinutes();
