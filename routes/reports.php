<?php

use App\Http\Controllers\AttendancePdfController;
use App\Http\Controllers\CertificatePdfController;
use App\Http\Controllers\FinancialPdfController;
use App\Http\Controllers\GradesPdfController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentListPdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Report / PDF Generation Routes
|--------------------------------------------------------------------------
|
| Middleware: auth
|
*/

Route::middleware(['auth'])->group(function () {

    Route::middleware(['feature:reports_basic'])->group(function () {
        Route::get('/reports/student-report/{student}', [ReportController::class, 'generateStudentReport'])->name('reports.student-report');
        Route::get('/reports/attendance-report/{section}', [ReportController::class, 'generateAttendanceReport'])->name('reports.attendance-report');
        Route::get('/reports/attendance/{section}/pdf', [AttendancePdfController::class, 'download'])->name('reports.attendance.pdf');
        Route::get('/reports/grades/{section}/pdf', [GradesPdfController::class, 'download'])->name('reports.grades.pdf');
        Route::get('/reports/students-list/{section}/pdf', [StudentListPdfController::class, 'download'])->name('reports.students.pdf');
    });

    Route::middleware(['feature:finance'])->group(function () {
        Route::get('/reports/financial/pdf', [FinancialPdfController::class, 'download'])->name('reports.financial.pdf');
        Route::get('/reports/financial/{student}', [FinancialPdfController::class, 'download'])->name('reports.financial-report');
        Route::get('/finance/ticket/{payment}', [FinancialPdfController::class, 'ticket'])->name('finance.ticket');
    });

    Route::middleware(['feature:reports_advanced'])->group(function () {
        Route::get('/reports/certificate/{student}/{course}/pdf', [CertificatePdfController::class, 'download'])->name('certificates.download');
    });
});
