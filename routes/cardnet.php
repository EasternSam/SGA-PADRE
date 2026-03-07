<?php

use App\Http\Controllers\CardnetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cardnet Payment Gateway Routes
|--------------------------------------------------------------------------
|
| Rutas para los callbacks de Cardnet (sin CSRF).
|
*/

Route::any('/cardnet/response', [CardnetController::class, 'handleResponse'])
    ->name('cardnet.response')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::any('/cardnet/cancel', [CardnetController::class, 'handleCancel'])
    ->name('cardnet.cancel')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Callbacks específicos del Kiosco
Route::any('/kiosk/cardnet/response', \App\Livewire\Kiosk\PaymentResult::class)
    ->name('kiosk.cardnet.response')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::any('/kiosk/cardnet/cancel', [CardnetController::class, 'handleKioskCancel'])
    ->name('kiosk.cardnet.cancel')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
