<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Portal Routes
|--------------------------------------------------------------------------
|
| Middleware: auth, role:Profesor|Admin
| Prefix: /teacher
|
*/

Route::middleware(['auth', 'role:Profesor|Admin'])->prefix('teacher')->group(function () {
    Route::middleware(['feature:academic'])->group(function () {
        Route::get('/dashboard', \App\Livewire\TeacherPortal\Dashboard::class)->name('teacher.dashboard');
        Route::get('/grades/{section}', \App\Livewire\TeacherPortal\Grades::class)->name('teacher.grades');
        Route::get('/attendance/{section}', \App\Livewire\TeacherPortal\Attendance::class)->name('teacher.attendance');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('teacher.profile.edit');
    });
});
