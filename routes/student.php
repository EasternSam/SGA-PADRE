<?php

use App\Http\Controllers\MoodleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentPortalController;
use App\Livewire\StudentPortal\CourseDetail as StudentPortalCourseDetail;
use App\Livewire\StudentPortal\Requests as StudentPortalRequests;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Portal Routes (Fase 16: React + Inertia)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Estudiante|Admin'])->prefix('student')->name('student.')->group(function () {

    Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/selection', [StudentPortalController::class, 'selection'])->name('selection');
    Route::post('/selection', [StudentPortalController::class, 'storeSelection'])->name('selection.store');
    Route::get('/payments', [StudentPortalController::class, 'payments'])->name('payments');
    Route::post('/payments/pay', [StudentPortalController::class, 'processPayment'])->name('payments.pay');
    Route::get('/projection', [StudentPortalController::class, 'projection'])->name('projection');

    Route::get('/course/{enrollmentId}', StudentPortalCourseDetail::class)->name('course.detail');
    Route::get('/requests', StudentPortalRequests::class)->name('requests');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    if (class_exists(\App\Livewire\StudentPortal\DocumentRepository::class)) {
        Route::get('/documents', \App\Livewire\StudentPortal\DocumentRepository::class)->name('documents');
    }

    if (class_exists(MoodleController::class)) {
        Route::get('/moodle-auth', [MoodleController::class, 'sso'])->name('moodle.auth');
    }
});
