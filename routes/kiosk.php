<?php

use App\Http\Controllers\KioskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Kiosk / Self-Service Touchscreen Routes (React 18 + Inertia)
|--------------------------------------------------------------------------
*/

Route::prefix('kiosk')->group(function () {
    // Touchscreen Auth
    Route::get('/login', [KioskController::class, 'login'])->name('kiosk.login');
    Route::post('/login', [KioskController::class, 'authenticate'])->name('kiosk.authenticate');

    // HR Touch Punch (Accessible without student login)
    Route::get('/hr-punch', [KioskController::class, 'hrPunch'])->name('kiosk.hr-punch');
    Route::post('/hr-punch', [KioskController::class, 'processPunch'])->name('kiosk.process-punch');

    // Protected Student Kiosk Routes
    Route::middleware(['auth.kiosk'])->group(function () {
        Route::post('/logout', function (Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('kiosk.login');
        })->name('kiosk.logout');

        Route::get('/dashboard', [KioskController::class, 'dashboard'])->name('kiosk.dashboard');
        Route::get('/finances', [KioskController::class, 'finances'])->name('kiosk.finances');
        Route::get('/schedule', [KioskController::class, 'schedule'])->name('kiosk.schedule');
        Route::get('/grades', [KioskController::class, 'grades'])->name('kiosk.grades');
        Route::get('/academic-offer', [KioskController::class, 'academicOffer'])->name('kiosk.academic-offer');
    });
});
