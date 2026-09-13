<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Auto-Backup Database Harian (Pukul 02:00 dini hari)
\Illuminate\Support\Facades\Schedule::command('db:backup --type=auto')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/db_backup.log'));

