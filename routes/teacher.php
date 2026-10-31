<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Portal Routes (Fase 17: React + Inertia)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Profesor|Admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/grades/{section?}', [TeacherPortalController::class, 'grades'])->name('grades');
    Route::post('/grades/{section}', [TeacherPortalController::class, 'saveGrades'])->name('grades.save');
    Route::get('/attendance/{section?}', [TeacherPortalController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/{section}', [TeacherPortalController::class, 'saveAttendance'])->name('attendance.save');
    Route::get('/payroll', [TeacherPortalController::class, 'payroll'])->name('payroll');

    if (class_exists(\App\Livewire\TeacherPortal\TeacherDocuments::class)) {
        Route::get('/documents', \App\Livewire\TeacherPortal\TeacherDocuments::class)->name('documents');
    }

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});
