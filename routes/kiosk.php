<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Kiosk / Self-Service Touchscreen Routes
|--------------------------------------------------------------------------
*/

Route::prefix('kiosk')->group(function () {
    // Auth
    Route::get('/login', \App\Livewire\Kiosk\Auth\Login::class)->name('kiosk.login');
    Route::get('/signup', \App\Livewire\Kiosk\Auth\Signup::class)->name('kiosk.signup');

    // Protected Kiosk Routes
    Route::middleware(['auth.kiosk'])->group(function () {
        Route::post('/logout', function (Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('kiosk.login');
        })->name('kiosk.logout');

        Route::get('/dashboard', \App\Livewire\Kiosk\Dashboard::class)->name('kiosk.dashboard');
        Route::get('/finances', \App\Livewire\Kiosk\Finances::class)->name('kiosk.finances');
        Route::get('/schedule', \App\Livewire\Kiosk\Schedule::class)->name('kiosk.schedule');
        Route::get('/grades', \App\Livewire\Kiosk\Grades::class)->name('kiosk.grades');
        Route::get('/academic-offer', \App\Livewire\Kiosk\AcademicOffer::class)->name('kiosk.academic-offer');
    });
});
