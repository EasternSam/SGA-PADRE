<?php

use App\Http\Controllers\CurriculumPdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Livewire\Admin\CertificateEditor;
use App\Livewire\Admin\CertificateTemplatesIndex;
use App\Livewire\Admin\ClassroomManagement;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\StudentRequestController;
use App\Http\Controllers\ClassroomController;

use App\Livewire\Admin\DatabaseImport;
use App\Livewire\Admin\EmailTester;
use App\Livewire\Admin\FinanceDashboard;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\PaymentConceptController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DgiiReportController;
use App\Http\Controllers\AccountingController;
use App\Livewire\Admin\Settings\Index as SystemSettingsIndex;
use App\Livewire\Admin\Inventory\Index as InventoryIndex;
use App\Livewire\Admissions\Index as AdmissionsIndex;
use App\Http\Controllers\AdminCalendarController;
use App\Livewire\Calendar\Index as CalendarIndex;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
|
| Middleware: auth, role:Admin|Registro|Contabilidad|Caja
| Prefix: /admin
|
*/

Route::middleware(['auth', 'role:Admin|Registro|Contabilidad|Caja'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // --- DASHBOARD MODULAR: diseño personal y predeterminados por rol (solo Admin) ---
    Route::put('/dashboard/layout', [\App\Http\Controllers\DashboardLayoutController::class, 'update'])->name('admin.dashboard.layout.update');
    Route::delete('/dashboard/layout', [\App\Http\Controllers\DashboardLayoutController::class, 'destroy'])->name('admin.dashboard.layout.destroy');
    Route::middleware(['role:Admin'])->group(function () {
        Route::put('/dashboard/layout/defaults/{role}', [\App\Http\Controllers\DashboardLayoutController::class, 'updateRoleDefault'])
            ->whereIn('role', \App\Services\Dashboard\WidgetRegistry::DASHBOARD_ROLES)->name('admin.dashboard.layout.defaults.update');
        Route::delete('/dashboard/layout/defaults/{role}', [\App\Http\Controllers\DashboardLayoutController::class, 'destroyRoleDefault'])
            ->whereIn('role', \App\Services\Dashboard\WidgetRegistry::DASHBOARD_ROLES)->name('admin.dashboard.layout.defaults.destroy');
    });

    // --- GESTIÓN DE MÓDULOS (Solo Admin) ---
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/system/modules', \App\Livewire\Admin\SystemModules::class)->name('admin.modules.index');
        Route::get('/scholarships', \App\Livewire\Admin\Scholarships\Index::class)->name('admin.scholarships.index');
    });

    // --- MODULO: GESTIÓN ACADÉMICA ---
    Route::middleware(['feature:academic', 'role:Admin|Registro'])->group(function () {
        Route::get('/students', [\App\Http\Controllers\StudentController::class, 'index'])->name('admin.students.index');
        Route::post('/students', [\App\Http\Controllers\StudentController::class, 'store'])->name('admin.students.store');
        Route::put('/students/{student}', [\App\Http\Controllers\StudentController::class, 'update'])->name('admin.students.update');
        Route::delete('/students/{student}', [\App\Http\Controllers\StudentController::class, 'destroy'])->name('admin.students.destroy');
        Route::post('/students/{student}/reset-kiosk-pin', [\App\Http\Controllers\StudentController::class, 'resetKioskPin'])->name('admin.students.reset-kiosk-pin');
        Route::get('/students/lookup-cedula', [\App\Http\Controllers\StudentController::class, 'lookupCedula'])->name('admin.students.lookup-cedula');
        Route::get('/students/profile/{student}', [\App\Http\Controllers\StudentController::class, 'profile'])->name('admin.students.profile');
        Route::get('/students/{student}/available-schedules', [\App\Http\Controllers\StudentController::class, 'availableSchedules'])->name('admin.students.available-schedules');
        Route::get('/students/{student}/enrollments/{enrollment}/available-schedules', [\App\Http\Controllers\StudentController::class, 'availableSchedulesForChange'])->name('admin.students.available-schedules-for-change');
        Route::post('/students/{student}/enroll', [\App\Http\Controllers\StudentController::class, 'enroll'])->name('admin.students.enroll');
        Route::post('/students/{student}/enrollments/{enrollment}/unenroll', [\App\Http\Controllers\StudentController::class, 'unenroll'])->name('admin.students.unenroll');
        Route::post('/students/{student}/enrollments/{enrollment}/change-section', [\App\Http\Controllers\StudentController::class, 'changeSection'])->name('admin.students.change-section');
        Route::post('/students/{student}/enrollments/{enrollment}/withdraw', [\App\Http\Controllers\StudentController::class, 'withdraw'])->name('admin.students.withdraw');
        Route::post('/students/{student}/enrollments/{enrollment}/re-enroll', [\App\Http\Controllers\StudentController::class, 'reEnroll'])->name('admin.students.re-enroll');
        Route::get('/students/{student}/kiosk-pin-pdf', [ReportController::class, 'printKioskPin'])->name('admin.students.kiosk-pin.print');

        // Cursos (Instituto)
        Route::middleware(['feature:academic_courses'])->group(function () {
            Route::get('/courses', [\App\Http\Controllers\AcademicCourseController::class, 'index'])->name('admin.courses.index');
            Route::post('/courses', [\App\Http\Controllers\AcademicCourseController::class, 'store'])->name('admin.courses.store');
            Route::put('/courses/{course}', [\App\Http\Controllers\AcademicCourseController::class, 'update'])->name('admin.courses.update');
            Route::delete('/courses/{course}', [\App\Http\Controllers\AcademicCourseController::class, 'destroy'])->name('admin.courses.destroy');
            Route::post('/courses/{course}/modules', [\App\Http\Controllers\AcademicCourseController::class, 'storeModule'])->name('admin.courses.modules.store');
            Route::put('/courses/modules/{module}', [\App\Http\Controllers\AcademicCourseController::class, 'updateModule'])->name('admin.courses.modules.update');
            Route::delete('/courses/modules/{module}', [\App\Http\Controllers\AcademicCourseController::class, 'destroyModule'])->name('admin.courses.modules.destroy');
            Route::post('/courses/modules/{module}/schedules', [\App\Http\Controllers\AcademicCourseController::class, 'storeSchedule'])->name('admin.courses.schedules.store');
            Route::put('/courses/schedules/{schedule}', [\App\Http\Controllers\AcademicCourseController::class, 'updateSchedule'])->name('admin.courses.schedules.update');
            Route::delete('/courses/schedules/{schedule}', [\App\Http\Controllers\AcademicCourseController::class, 'destroySchedule'])->name('admin.courses.schedules.destroy');

            // Endpoints de Integración WordPress & Moodle
            Route::get('/courses/wp-courses', [\App\Http\Controllers\AcademicCourseController::class, 'getWpCourses'])->name('admin.courses.wp-courses');
            Route::post('/courses/{course}/link-wp', [\App\Http\Controllers\AcademicCourseController::class, 'linkWpCourse'])->name('admin.courses.link-wp');
            Route::get('/courses/moodle-courses', [\App\Http\Controllers\AcademicCourseController::class, 'getMoodleCourses'])->name('admin.courses.moodle-courses');
            Route::post('/courses/link-moodle', [\App\Http\Controllers\AcademicCourseController::class, 'linkMoodle'])->name('admin.courses.link-moodle');
            Route::get('/courses/schedules/{schedule}/wp-schedules', [\App\Http\Controllers\AcademicCourseController::class, 'getWpSchedules'])->name('admin.courses.schedules.wp-schedules');
            Route::post('/courses/schedules/{schedule}/link-wp', [\App\Http\Controllers\AcademicCourseController::class, 'linkWpSchedule'])->name('admin.courses.schedules.link-wp');
        });

        // Carreras (Universidad)
        Route::middleware(['feature:academic_careers'])->group(function () {
            Route::get('/careers', [\App\Http\Controllers\CareerController::class, 'index'])->name('admin.careers.index');
            Route::post('/careers', [\App\Http\Controllers\CareerController::class, 'store'])->name('admin.careers.store');
            Route::put('/careers/{career}', [\App\Http\Controllers\CareerController::class, 'update'])->name('admin.careers.update');
            Route::delete('/careers/{career}', [\App\Http\Controllers\CareerController::class, 'destroy'])->name('admin.careers.destroy');
            Route::get('/careers/{career}/curriculum', [\App\Http\Controllers\CareerController::class, 'curriculum'])->name('admin.careers.curriculum');
            Route::post('/careers/{career}/subjects', [\App\Http\Controllers\CareerController::class, 'storeSubject'])->name('admin.careers.subjects.store');
            Route::put('/careers/{career}/subjects/{subject}', [\App\Http\Controllers\CareerController::class, 'updateSubject'])->name('admin.careers.subjects.update');
            Route::delete('/careers/{career}/subjects/{subject}', [\App\Http\Controllers\CareerController::class, 'destroySubject'])->name('admin.careers.subjects.destroy');
            Route::get('/careers/{career}/curriculum/pdf', [CurriculumPdfController::class, 'download'])->name('admin.careers.curriculum.pdf');
            Route::post('/careers/{career}/subjects/{subject}/link-moodle', [\App\Http\Controllers\CareerController::class, 'linkSubjectMoodle'])->name('admin.careers.subjects.link-moodle');
        });

        // Calendario Académico React (Untitled UI)
        Route::get('/calendar', [AdminCalendarController::class, 'index'])->name('admin.calendar.index');
        Route::post('/calendar/events', [AdminCalendarController::class, 'store'])->name('admin.calendar.events.store');
        Route::put('/calendar/events/{event}', [AdminCalendarController::class, 'update'])->name('admin.calendar.events.update');
        Route::delete('/calendar/events/{event}', [AdminCalendarController::class, 'destroy'])->name('admin.calendar.events.destroy');

        // Admisiones & Aspirantes (Fase 12)
        Route::get('/admissions', [AdmissionController::class, 'index'])->name('admin.admissions.index');
        Route::post('/admissions/{admission}/review', [AdmissionController::class, 'saveReview'])->name('admin.admissions.review');
        Route::post('/admissions/{admission}/approve', [AdmissionController::class, 'approve'])->name('admin.admissions.approve');
        Route::post('/admissions/{admission}/reject', [AdmissionController::class, 'reject'])->name('admin.admissions.reject');


        Route::get('/teachers', [\App\Http\Controllers\TeacherController::class, 'index'])->name('admin.teachers.index');
        Route::post('/teachers', [\App\Http\Controllers\TeacherController::class, 'store'])->name('admin.teachers.store');
        Route::put('/teachers/{teacher}', [\App\Http\Controllers\TeacherController::class, 'update'])->name('admin.teachers.update');
        Route::delete('/teachers/{teacher}', [\App\Http\Controllers\TeacherController::class, 'destroy'])->name('admin.teachers.destroy');
        Route::get('/teachers/course-modules/{course}', [\App\Http\Controllers\TeacherController::class, 'courseModules'])->name('admin.teachers.course-modules');
        Route::get('/teachers/profile/{teacher}', [\App\Http\Controllers\TeacherController::class, 'profile'])->name('admin.teachers.profile');
        Route::post('/teachers/{teacher}/assign-schedule', [\App\Http\Controllers\TeacherController::class, 'assignSchedule'])->name('admin.teachers.assign-schedule');
        Route::post('/teachers/{teacher}/create-schedule', [\App\Http\Controllers\TeacherController::class, 'createSchedule'])->name('admin.teachers.create-schedule');
        Route::post('/teachers/{teacher}/unassign-schedule/{schedule}', [\App\Http\Controllers\TeacherController::class, 'unassignSchedule'])->name('admin.teachers.unassign-schedule');
        // Trámites y Solicitudes Estudiantiles (Fase 12)
        Route::get('/requests', [StudentRequestController::class, 'index'])->name('admin.requests');
        Route::post('/requests', [StudentRequestController::class, 'storeRequest'])->name('admin.requests.store');
        Route::post('/requests/{studentRequest}/status', [StudentRequestController::class, 'updateStatus'])->name('admin.requests.update-status');
        Route::get('/requests/search-students', [StudentRequestController::class, 'searchStudents'])->name('admin.requests.search-students');
        Route::post('/requests/types', [StudentRequestController::class, 'storeType'])->name('admin.requests.types.store');
        Route::put('/requests/types/{requestType}', [StudentRequestController::class, 'updateType'])->name('admin.requests.types.update');
        Route::delete('/requests/types/{requestType}', [StudentRequestController::class, 'destroyType'])->name('admin.requests.types.destroy');

        Route::middleware(['role:Admin'])->group(function () {
            // Aulas y Espacios Físicos (Fase 11)
            Route::get('/classrooms', [ClassroomController::class, 'index'])->name('admin.classrooms.index');
            Route::post('/classrooms', [ClassroomController::class, 'storeClassroom'])->name('admin.classrooms.store');
            Route::put('/classrooms/{classroom}', [ClassroomController::class, 'updateClassroom'])->name('admin.classrooms.update');
            Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroyClassroom'])->name('admin.classrooms.destroy');
            Route::get('/classrooms/{classroom}/schedule', [ClassroomController::class, 'getSchedule'])->name('admin.classrooms.schedule');
            Route::post('/classrooms/buildings', [ClassroomController::class, 'storeBuilding'])->name('admin.classrooms.buildings.store');
            Route::post('/classrooms/reservations', [ClassroomController::class, 'storeReservation'])->name('admin.classrooms.reservations.store');
            Route::delete('/classrooms/reservations/{reservation}', [ClassroomController::class, 'destroyReservation'])->name('admin.classrooms.reservations.destroy');

        });
    });

    // --- MODULO: INVENTARIO ---
    Route::middleware(['feature:inventory', 'role:Admin|Contabilidad'])->group(function () {
        if (class_exists(InventoryIndex::class)) {
            Route::get('/inventory', InventoryIndex::class)->name('admin.inventory.index');
        } else {
            Route::get('/inventory', function () {
                return 'Módulo de inventario no instalado';
            })->name('admin.inventory.index');
        }
    });

    // --- MODULO: FINANZAS ---
    Route::middleware(['feature:finance'])->group(function () {
        Route::middleware(['role:Admin|Contabilidad|Caja'])->group(function () {
            Route::get('/finance/dashboard', [FinanceDashboardController::class, 'index'])->name('admin.finance.dashboard');
        });

        Route::middleware(['role:Admin|Contabilidad'])->group(function () {
            Route::get('/finance/payment-concepts', [PaymentConceptController::class, 'index'])->name('admin.finance.concepts');
            Route::post('/finance/payment-concepts', [PaymentConceptController::class, 'store'])->name('admin.finance.concepts.store');
            Route::delete('/finance/payment-concepts/{id}', [PaymentConceptController::class, 'destroy'])->name('admin.finance.concepts.destroy');
            Route::post('/finance/payment-concepts/mass-delete', [PaymentConceptController::class, 'massDestroy'])->name('admin.finance.concepts.mass-delete');
            Route::get('/finance/chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('admin.finance.chart-of-accounts');
            Route::post('/finance/chart-of-accounts', [AccountingController::class, 'storeAccount'])->name('admin.finance.chart-of-accounts.store');
            Route::delete('/finance/chart-of-accounts/{id}', [AccountingController::class, 'destroyAccount'])->name('admin.finance.chart-of-accounts.destroy');
            Route::get('/finance/manual-entry', [AccountingController::class, 'manualEntry'])->name('admin.finance.manual-entry');
            Route::post('/finance/manual-entry', [AccountingController::class, 'storeManualEntry'])->name('admin.finance.manual-entry.store');
            Route::get('/finance/expenses', [ExpenseController::class, 'index'])->name('admin.finance.expenses');
            Route::post('/finance/expenses', [ExpenseController::class, 'store'])->name('admin.finance.expenses.store');
            Route::post('/finance/expenses/pay', [ExpenseController::class, 'pay'])->name('admin.finance.expenses.pay');
            Route::get('/finance/expenses/lookup-rnc', [ExpenseController::class, 'lookupRnc'])->name('admin.finance.expenses.lookup-rnc');
            Route::get('/finance/period-closing', [AccountingController::class, 'periodClosing'])->name('admin.finance.period-closing');
            Route::post('/finance/period-closing', [AccountingController::class, 'storePeriodClosing'])->name('admin.finance.period-closing.store');
            Route::get('/finance/dgii-reports', [DgiiReportController::class, 'index'])->name('admin.finance.dgii-reports');
            Route::get('/finance/dgii-reports/download-606', [DgiiReportController::class, 'download606'])->name('admin.finance.dgii-reports.download-606');
            Route::get('/finance/dgii-reports/download-607', [DgiiReportController::class, 'download607'])->name('admin.finance.dgii-reports.download-607');
            Route::get('/finance/statements', [AccountingController::class, 'statements'])->name('admin.finance.statements');
            Route::get('/finance/ledger', [AccountingController::class, 'ledger'])->name('admin.finance.ledger');
        });
    });

    // --- MODULO: RECURSOS HUMANOS (Fase 15) ---
    Route::middleware(['role:Admin|Contabilidad'])->group(function () {
        Route::get('/hr/employees', [EmployeeController::class, 'index'])->name('admin.hr.employees');
        Route::post('/hr/employees', [EmployeeController::class, 'store'])->name('admin.hr.employees.store');
        Route::put('/hr/employees/{employee}', [EmployeeController::class, 'update'])->name('admin.hr.employees.update');
        Route::get('/hr/employees/{employee}/profile', [EmployeeController::class, 'profile'])->name('admin.hr.employees.profile');
        Route::post('/hr/employees/{employee}/salary', [EmployeeController::class, 'updateSalary'])->name('admin.hr.employees.salary');
        Route::post('/hr/employees/{employee}/role', [EmployeeController::class, 'updateRole'])->name('admin.hr.employees.role');
        Route::post('/hr/employees/{employee}/biometric', [EmployeeController::class, 'updateBiometric'])->name('admin.hr.employees.biometric');
        Route::post('/hr/employees/{employee}/event', [EmployeeController::class, 'storeEvent'])->name('admin.hr.employees.event');
        Route::post('/hr/employees/{employee}/pin', [EmployeeController::class, 'regeneratePin'])->name('admin.hr.employees.pin');
        Route::post('/hr/employees/{employee}/ban', [EmployeeController::class, 'banUser'])->name('admin.hr.employees.ban');

        Route::get('/hr/attendances', [EmployeeAttendanceController::class, 'index'])->name('admin.hr.attendances');
        Route::post('/hr/attendances', [EmployeeAttendanceController::class, 'storePunch'])->name('admin.hr.attendances.store');
        Route::delete('/hr/attendances/{attendance}', [EmployeeAttendanceController::class, 'destroyPunch'])->name('admin.hr.attendances.destroy');

        Route::get('/hr/payroll', [PayrollController::class, 'index'])->name('admin.hr.payroll');
        Route::post('/hr/payroll/generate', [PayrollController::class, 'generate'])->name('admin.hr.payroll.generate');
        Route::post('/hr/payroll/{payroll}/pay', [PayrollController::class, 'approveAndPay'])->name('admin.hr.payroll.pay');
        Route::delete('/hr/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('admin.hr.payroll.destroy');
        Route::get('/hr/payroll/{payroll}/items', [PayrollController::class, 'items'])->name('admin.hr.payroll.items');
    });

    // --- MODULO: REPORTES AVANZADOS / DIPLOMAS ---
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/certificates', [\App\Http\Controllers\AdminCertificateController::class, 'index'])->name('admin.certificates.index');
        Route::get('/certificate-templates', [\App\Http\Controllers\AdminCertificateController::class, 'index'])->name('admin.certificates.templates');
        Route::get('/certificate-editor/{templateId?}', [\App\Http\Controllers\AdminCertificateController::class, 'editor'])->name('admin.certificates.editor');
        Route::post('/certificate-editor/save', [\App\Http\Controllers\AdminCertificateController::class, 'saveTemplate'])->name('admin.certificates.save');
        Route::delete('/certificate-templates/{template}', [\App\Http\Controllers\AdminCertificateController::class, 'destroy'])->name('admin.certificates.destroy');
    });

    // --- REPORTES BÁSICOS ---
    Route::middleware(['feature:reports_basic', 'role:Admin|Registro|Contabilidad'])->group(function () {
        Route::get('/reports', [\App\Http\Controllers\ReportCenterController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-csv', [\App\Http\Controllers\ReportCenterController::class, 'exportCsv'])->name('reports.export-csv');
    });

    // --- CONFIGURACIÓN GLOBAL (Solo Admin) ---
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/import', DatabaseImport::class)->name('admin.import');
        Route::get('/email-tester', EmailTester::class)->name('admin.email-tester');
        Route::get('/users', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('/users', [\App\Http\Controllers\AdminUserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/activity-logs', [\App\Http\Controllers\AdminAuditLogController::class, 'index'])->name('admin.activity-logs.index');

        Route::get('/settings', [\App\Http\Controllers\AdminSettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings', [\App\Http\Controllers\AdminSettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('/settings/pairing-code', [\App\Http\Controllers\AdminSettingsController::class, 'generatePairingCode'])->name('admin.settings.pairing-code');
        Route::post('/settings/sync-wp-students', [\App\Http\Controllers\AdminSettingsController::class, 'syncWordPressStudents'])->name('admin.settings.sync-wp');
        Route::post('/settings/test-wp', [\App\Http\Controllers\AdminSettingsController::class, 'testWordpress'])->name('admin.settings.test-wp');
        Route::post('/settings/test-moodle', [\App\Http\Controllers\AdminSettingsController::class, 'testMoodle'])->name('admin.settings.test-moodle');
        Route::post('/settings/test-bills', [\App\Http\Controllers\AdminSettingsController::class, 'testBills'])->name('admin.settings.test-bills');
    });

        // --- PILOTO INERTIA + REACT: TERMINAL DE CAJA (POS) ---
    Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('admin.pos');
    Route::get('/pos/search-student', [\App\Http\Controllers\PosController::class, 'searchStudents'])->name('admin.pos.search');
    Route::post('/pos/process', [\App\Http\Controllers\PosController::class, 'process'])->name('admin.pos.process');

    // --- CENTRO DE NOTIFICACIONES (UI Arc) ---
    Route::prefix('notifications')->name('admin.notifications.')->group(function () {
        Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/clear-read', [\App\Http\Controllers\NotificationController::class, 'clearRead'])->name('clear-read');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
});