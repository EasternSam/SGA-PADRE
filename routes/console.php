<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- PROGRAMACIÓN DE PAGOS MENSUALES ---
// Ejecutar diariamente a las 6:00 AM
Schedule::command('sga:process-monthly-payments')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));