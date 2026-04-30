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

// --- DETECCIÓN DE DESERCIÓN SILENCIOSA ---
// Ejecutar semanalmente los domingos a las 3:00 AM
Schedule::command('sga:detect-inactive-students')
    ->weeklyOn(0, '03:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// --- LIMPIEZA DE AUDITORÍA DE LOGIN ---
// Eliminar registros de login_attempts mayores a 90 días
Schedule::call(function () {
    $deleted = \App\Models\LoginAttempt::pruneOlderThan(90);
    \Illuminate\Support\Facades\Log::info("[SECURITY] Purgados {$deleted} registros de login_attempts > 90 días");
})
    ->weeklyOn(0, '04:00')
    ->appendOutputTo(storage_path('logs/scheduler.log'));