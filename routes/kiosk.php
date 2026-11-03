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
    // Entry point redirect
    Route::get('/', function (Request $request) {
        $hasKioskSession = $request->session()->has('kiosk_student_id')
            || (Auth::check() && !$request->session()->get('kiosk_logged_out', false));

        return $hasKioskSession
            ? redirect()->route('kiosk.dashboard')
            : redirect()->route('kiosk.login');
    })->name('kiosk.index');

    // Touchscreen Auth & Self-Registration
    Route::get('/login', [KioskController::class, 'login'])->name('kiosk.login');
    Route::post('/login', [KioskController::class, 'authenticate'])->name('kiosk.authenticate');
    Route::get('/signup', [KioskController::class, 'signup'])->name('kiosk.signup');
    Route::post('/signup', [KioskController::class, 'storeSignup'])->name('kiosk.signup.store');

    // Public Kiosk Self-Service Modules (accessible with or without student login)
    Route::get('/hr-punch', [KioskController::class, 'hrPunch'])->name('kiosk.hr-punch');
    Route::post('/hr-punch', [KioskController::class, 'processPunch'])->name('kiosk.process-punch');
    Route::get('/attendance', [KioskController::class, 'hrPunch'])->name('kiosk.attendance');
    Route::post('/attendance', [KioskController::class, 'processPunch'])->name('kiosk.attendance.store');
    Route::get('/academic-offer', [KioskController::class, 'academicOffer'])->name('kiosk.academic-offer');
    Route::post('/academic-offer/enroll', [KioskController::class, 'enrollCourse'])->name('kiosk.academic-offer.enroll');

    // Logout (works cleanly without destroying Admin web sessions)
    Route::post('/logout', function (Request $request) {
        $request->session()->forget('kiosk_student_id');
        $request->session()->put('kiosk_logged_out', true);

        if (Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Estudiante')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('kiosk.login');
    })->name('kiosk.logout');

    // Protected Student Kiosk Routes
    Route::middleware(['auth.kiosk'])->group(function () {
        Route::get('/dashboard', [KioskController::class, 'dashboard'])->name('kiosk.dashboard');
        Route::get('/finances', [KioskController::class, 'finances'])->name('kiosk.finances');
        Route::post('/finances/pay', [KioskController::class, 'processPayment'])->name('kiosk.finances.pay');
        Route::get('/schedule', [KioskController::class, 'schedule'])->name('kiosk.schedule');
        Route::get('/grades', [KioskController::class, 'grades'])->name('kiosk.grades');
    });
});
