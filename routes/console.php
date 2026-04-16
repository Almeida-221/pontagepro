<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expire les abonnements dont la date de fin est dépassée — exécuté chaque jour à minuit
Schedule::command('subscriptions:expire')->dailyAt('00:00');
