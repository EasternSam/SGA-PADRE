<?php

use App\Http\Controllers\MoodleController;
use App\Http\Controllers\ProfileController;
use App\Livewire\StudentPortal\Dashboard as StudentPortalDashboard;
use App\Livewire\StudentPortal\CourseDetail as StudentPortalCourseDetail;
use App\Livewire\StudentPortal\MyPayments as StudentPortalPayments;
use App\Livewire\StudentPortal\Requests as StudentPortalRequests;
use App\Livewire\StudentPortal\SubjectSelection as StudentPortalSelection;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Portal Routes
|--------------------------------------------------------------------------
|
| Middleware: auth, role:Estudiante
| Prefix: /student
| Name prefix: student.
|
*/

Route::middleware(['auth', 'role:Estudiante'])->prefix('student')->name('student.')->group(function () {

    Route::middleware(['feature:academic'])->group(function () {
        Route::get('/dashboard', StudentPortalDashboard::class)->name('dashboard');
        Route::get('/course/{enrollmentId}', StudentPortalCourseDetail::class)->name('course.detail');
        Route::get('/requests', StudentPortalRequests::class)->name('requests');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

        Route::middleware(['feature:academic_careers'])->group(function () {
            Route::get('/selection', StudentPortalSelection::class)->name('selection');
        });
    });

    Route::middleware(['feature:finance'])->group(function () {
        Route::get('/payments', StudentPortalPayments::class)->name('payments');
    });

    Route::middleware(['feature:virtual_classroom'])->group(function () {
        Route::get('/moodle-auth', [MoodleController::class, 'sso'])->name('moodle.auth');
    });
});
