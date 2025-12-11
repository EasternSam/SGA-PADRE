<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentListPdfController;
use App\Http\Controllers\GradesPdfController;
use App\Http\Controllers\FinancialPdfController; // <-- Asegúrate de importar esto
use App\Http\Controllers\AttendancePdfController;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Students\Index as StudentsIndex;
use App\Livewire\StudentProfile\Index as StudentProfileIndex;
use App\Livewire\Courses\Index as CoursesIndex;
use App\Livewire\Teachers\Index as TeachersIndex;
use App\Livewire\TeacherProfile\Index as TeacherProfileIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Admin\DatabaseImport;
use App\Livewire\Admin\RequestsManagement;

// Portal Estudiante Livewire
use App\Livewire\StudentPortal\Dashboard as StudentDashboard;
use App\Livewire\StudentPortal\CourseDetail as StudentCourseDetail;
use App\Livewire\StudentPortal\MyPayments as StudentMyPayments;
use App\Livewire\StudentPortal\Requests as StudentRequests;

// Portal Profesor Livewire
use App\Livewire\TeacherPortal\Dashboard as TeacherDashboard;
use App\Livewire\TeacherPortal\Attendance as TeacherAttendance;
use App\Livewire\TeacherPortal\Grades as TeacherGrades;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // --- Rutas de Administración ---
    Route::middleware(['role:Administrador|Secretaria'])->group(function () {
        Route::get('/students', StudentsIndex::class)->name('students.index');
        Route::get('/admin/students/profile/{student}', StudentProfileIndex::class)->name('students.profile');
        
        Route::get('/courses', CoursesIndex::class)->name('courses.index');
        
        Route::get('/teachers', TeachersIndex::class)->name('teachers.index');
        Route::get('/teachers/profile/{user}', TeacherProfileIndex::class)->name('teachers.profile');
        
        Route::get('/reports', ReportsIndex::class)->name('reports.index');
        Route::get('/admin/database-import', DatabaseImport::class)->name('admin.database-import');
        Route::get('/admin/requests', RequestsManagement::class)->name('admin.requests');
    });

    // --- Rutas de Reportes PDF ---
    Route::get('/reports/student/{student}', [ReportController::class, 'generateStudentReport'])->name('reports.student-report');
    Route::get('/reports/student-list/{courseSchedule}', [StudentListPdfController::class, 'generate'])->name('reports.student-list-pdf');
    Route::get('/reports/grades/{courseSchedule}', [GradesPdfController::class, 'generate'])->name('reports.grades-pdf');
    
    // --> AQUÍ AGREGAMOS LA RUTA FALTANTE DEL REPORTE FINANCIERO <--
    Route::get('/reports/financial/{student}', [FinancialPdfController::class, 'generate'])->name('reports.financial-report');
    
    Route::get('/reports/attendance/{courseSchedule}', [AttendancePdfController::class, 'generate'])->name('reports.attendance-pdf');

    // --- Rutas del Portal Estudiante ---
    Route::middleware(['role:Estudiante'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', StudentDashboard::class)->name('dashboard');
        Route::get('/course/{enrollmentId}', StudentCourseDetail::class)->name('course-detail');
        Route::get('/payments', StudentMyPayments::class)->name('payments');
        Route::get('/requests', StudentRequests::class)->name('requests');
    });

    // --- Rutas del Portal Profesor ---
    Route::middleware(['role:Profesor'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');
        Route::get('/attendance/{scheduleId}', TeacherAttendance::class)->name('attendance');
        Route::get('/grades/{scheduleId}', TeacherGrades::class)->name('grades');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';