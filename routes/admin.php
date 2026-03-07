<?php

use App\Http\Controllers\CurriculumPdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Livewire\Admin\CertificateEditor;
use App\Livewire\Admin\CertificateTemplatesIndex;
use App\Livewire\Admin\ClassroomManagement;
use App\Livewire\Admin\DatabaseImport;
use App\Livewire\Admin\EmailTester;
use App\Livewire\Admin\FinanceDashboard;
use App\Livewire\Admin\Settings\Index as SystemSettingsIndex;
use App\Livewire\Admin\Inventory\Index as InventoryIndex;
use App\Livewire\Admissions\Index as AdmissionsIndex;
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

    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('admin.dashboard');

    // --- GESTIÓN DE MÓDULOS (Solo Admin) ---
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/system/modules', \App\Livewire\Admin\SystemModules::class)->name('admin.modules.index');
    });

    // --- MODULO: GESTIÓN ACADÉMICA ---
    Route::middleware(['feature:academic', 'role:Admin|Registro'])->group(function () {
        Route::get('/students', \App\Livewire\Students\Index::class)->name('admin.students.index');
        Route::get('/students/profile/{student}', \App\Livewire\StudentProfile\Index::class)->name('admin.students.profile');
        Route::get('/students/{student}/kiosk-pin-pdf', [ReportController::class, 'printKioskPin'])->name('admin.students.kiosk-pin.print');

        // Cursos (Instituto)
        Route::middleware(['feature:academic_courses'])->group(function () {
            Route::get('/courses', \App\Livewire\Courses\Index::class)->name('admin.courses.index');
        });

        // Carreras (Universidad)
        Route::middleware(['feature:academic_careers'])->group(function () {
            Route::get('/careers', \App\Livewire\Careers\Index::class)->name('admin.careers.index');
            Route::get('/careers/{career}/curriculum', \App\Livewire\Careers\Curriculum::class)->name('admin.careers.curriculum');
            Route::get('/careers/{career}/curriculum/pdf', [CurriculumPdfController::class, 'download'])->name('admin.careers.curriculum.pdf');
        });

        if (class_exists(CalendarIndex::class)) {
            Route::get('/calendar', CalendarIndex::class)->name('admin.calendar.index');
        }

        Route::middleware(['feature:academic_careers'])->group(function () {
            if (class_exists(AdmissionsIndex::class)) {
                Route::get('/admissions', AdmissionsIndex::class)->name('admin.admissions.index');
            }
        });

        Route::get('/teachers', \App\Livewire\Teachers\Index::class)->name('admin.teachers.index');
        Route::get('/teachers/profile/{teacher}', \App\Livewire\TeacherProfile\Index::class)->name('admin.teachers.profile');
        Route::get('/requests', \App\Livewire\Admin\RequestsManagement::class)->name('admin.requests');

        Route::middleware(['role:Admin'])->group(function () {
            Route::get('/classrooms', ClassroomManagement::class)->name('admin.classrooms.index');
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
            Route::get('/finance/dashboard', FinanceDashboard::class)->name('admin.finance.dashboard');
        });

        Route::middleware(['role:Admin|Contabilidad'])->group(function () {
            Route::get('/finance/payment-concepts', \App\Livewire\Finance\PaymentConcepts::class)->name('admin.finance.concepts');
            Route::get('/finance/chart-of-accounts', \App\Livewire\Admin\ChartOfAccounts::class)->name('admin.finance.chart-of-accounts');
            Route::get('/finance/manual-entry', \App\Livewire\Admin\ManualJournalEntry::class)->name('admin.finance.manual-entry');
            Route::get('/finance/expenses', \App\Livewire\Admin\Finance\Expenses::class)->name('admin.finance.expenses');
            Route::get('/finance/period-closing', \App\Livewire\Admin\Finance\PeriodClosing::class)->name('admin.finance.period-closing');
            Route::get('/finance/dgii-reports', \App\Livewire\Admin\Finance\DgiiReports::class)->name('admin.finance.dgii-reports');
            Route::get('/finance/statements', \App\Livewire\Admin\FinancialStatements::class)->name('admin.finance.statements');
            Route::get('/finance/ledger', \App\Livewire\Admin\AccountingLedger::class)->name('admin.finance.ledger');
        });
    });

    // --- MODULO: REPORTES AVANZADOS / DIPLOMAS ---
    Route::middleware(['feature:reports_advanced', 'role:Admin'])->group(function () {
        Route::get('/certificates', \App\Livewire\Certificates\Index::class)->name('admin.certificates.index');
        Route::get('/certificate-templates', CertificateTemplatesIndex::class)->name('admin.certificates.templates');
        Route::get('/certificate-editor', CertificateEditor::class)->name('admin.certificates.editor');
        Route::get('/certificate-editor/{templateId?}', CertificateEditor::class)->name('admin.certificates.edit');
    });

    // --- REPORTES BÁSICOS ---
    Route::middleware(['feature:reports_basic', 'role:Admin|Registro|Contabilidad'])->group(function () {
        Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
    });

    // --- CONFIGURACIÓN GLOBAL (Solo Admin) ---
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/import', DatabaseImport::class)->name('admin.import');
        Route::get('/email-tester', EmailTester::class)->name('admin.email-tester');
        Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('admin.users.index');
        Route::get('/activity-logs', \App\Livewire\Admin\ActivityLogs\Index::class)->name('admin.activity-logs.index');
        Route::get('/settings', SystemSettingsIndex::class)->name('admin.settings.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
});
