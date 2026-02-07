<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

// Controllers
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendancePdfController; 
use App\Http\Controllers\GradesPdfController; 
use App\Http\Controllers\FinancialPdfController;
use App\Http\Controllers\StudentListPdfController; 
use App\Http\Controllers\CertificatePdfController; 
use App\Http\Controllers\CurriculumPdfController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;

// Services & Models
use App\Services\EcfService;
use App\Services\MatriculaService;
use App\Models\Payment;
use App\Models\User;
use App\Mail\PaymentReceiptMail;

// Livewire Components (Admin)
use App\Livewire\Admin\DatabaseImport; 
use App\Livewire\Admin\CertificateEditor;
use App\Livewire\Admin\CertificateTemplatesIndex;
use App\Livewire\Admin\ClassroomManagement;
use App\Livewire\Admin\FinanceDashboard;
use App\Livewire\Admin\EmailTester;
use App\Livewire\Admin\RequestsManagement;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;

// Livewire Components (Student)
use App\Livewire\StudentPortal\Dashboard as StudentPortalDashboard;
use App\Livewire\StudentPortal\CourseDetail as StudentPortalCourseDetail;
use App\Livewire\StudentPortal\Requests as StudentPortalRequests;
use App\Livewire\StudentPortal\MyPayments as StudentPortalPayments; 
use App\Livewire\StudentPortal\SubjectSelection as StudentPortalSelection;

// Livewire Components (Teacher)
use App\Livewire\TeacherPortal\Dashboard as TeacherPortalDashboard;
use App\Livewire\TeacherPortal\Grades as TeacherPortalGrades;
use App\Livewire\TeacherPortal\Attendance as TeacherPortalAttendance;

// Livewire Components (Shared/Other)
use App\Livewire\Admissions\Index as AdmissionsIndex;
use App\Livewire\Admissions\Register as AdmissionsRegister;
use App\Livewire\Applicant\Dashboard as ApplicantDashboard;
use App\Livewire\Calendar\Index as CalendarIndex;
use App\Livewire\Dashboard\Index as AdminDashboardIndex;
use App\Livewire\Students\Index as StudentsIndex;
use App\Livewire\StudentProfile\Index as StudentProfileIndex;
use App\Livewire\Courses\Index as CoursesIndex;
use App\Livewire\Careers\Index as CareersIndex;
use App\Livewire\Careers\Curriculum as CareersCurriculum;
use App\Livewire\Teachers\Index as TeachersIndex;
use App\Livewire\TeacherProfile\Index as TeacherProfileIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Certificates\Index as CertificatesIndex;
use App\Livewire\Finance\PaymentConcepts as FinanceConcepts;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

// --- REGISTRO DE ESTUDIANTES / ASPIRANTES ---
Route::middleware('guest')->group(function () {
    Route::get('/registro-estudiantes', [RegisteredUserController::class, 'create'])->name('student.register.link');
    Route::post('/registro-estudiantes', [RegisteredUserController::class, 'store'])->name('student.register.store');
});

// ==============================================================================
// RUTAS DE CARDNET (CALLBACKS)
// ==============================================================================
// Nota: Estas rutas excluyen protección CSRF en el RouteServiceProvider o via 'withoutMiddleware'
Route::prefix('cardnet')->name('cardnet.')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function () {
    
    // 1. Respuesta (Éxito/Fallo)
    Route::any('/response', function (Request $request, EcfService $ecfService, MatriculaService $matriculaService) {
        Log::info('Cardnet Debug: Retorno recibido', $request->all());

        $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');
        $responseCode = $request->input('ResponseCode');
        $authCode = $request->input('AuthorizationCode');
        $txId = $request->input('TransactionId');
        $responseMessage = $request->input('ResponseMessage') ?? $request->input('ResponseMsg') ?? 'Transacción declinada';

        $payment = Payment::find($orderId);

        if (!$payment) {
            Log::error('Cardnet Error: Pago no encontrado ID: ' . $orderId);
            return redirect('/')->with('error', 'Error crítico: Pago no encontrado.');
        }

        // Auto-login de emergencia si se perdió sesión
        if (!Auth::check() && $payment->user_id) {
            Log::warning("Cardnet: Restaurando sesión usuario {$payment->user_id}");
            Auth::loginUsingId($payment->user_id);
        }

        if ($responseCode === '00') {
            // --- APROBADO ---
            $payment->update([
                'status' => 'Completado',
                'transaction_id' => $authCode,
                'notes' => "Aprobado Cardnet | Ref: {$txId} | Auth: {$authCode}",
            ]);

            try {
                // Generar comprobante fiscal
                $ecfService->emitirComprobante($payment);
                
                // Activar inscripción si aplica
                if ($payment->enrollment) {
                    $payment->enrollment->update(['status' => 'Cursando']);
                }
                
                // Generar matrícula si es nuevo ingreso
                $student = $payment->student;
                if ($student && !$student->student_code && $payment->paymentConcept && stripos($payment->paymentConcept->name, 'Inscripción') !== false) {
                    $matriculaService->generarMatricula($payment);
                }

                // Enviar recibo por correo
                if ($student && $student->email) {
                    try {
                        $payment->load('student', 'paymentConcept', 'enrollment.courseSchedule.module');
                        $pdfOutput = Pdf::loadView('reports.thermal-invoice', ['payment' => $payment])->output();
                        $pdfBase64 = base64_encode($pdfOutput);
                        Mail::to($student->email)->send(new PaymentReceiptMail($payment, $pdfBase64));
                    } catch (\Exception $e) {
                        Log::error("Cardnet Error Email: " . $e->getMessage());
                    }
                }

            } catch (\Exception $e) {
                Log::error("Cardnet Error Lógica Negocio: " . $e->getMessage());
            }

            // Redirección
            $user = Auth::user();
            if ($user && $user->hasRole('Estudiante')) {
                return redirect()->route('student.payments')->with('message', '¡Pago realizado con éxito! Código: ' . $authCode);
            }
            return redirect('/dashboard')->with('message', 'Pago procesado correctamente.');

        } else {
            // --- RECHAZADO ---
            $payment->update([
                'status' => 'Pendiente', 
                'notes' => "Fallido [{$responseCode}]: {$responseMessage}",
            ]);
            Log::warning("Cardnet Rechazo Orden {$orderId}: {$responseMessage}");
            return redirect()->route('student.payments')->with('error', "Pago rechazado: {$responseMessage}");
        }
    })->name('response');

    // 2. Cancelación
    Route::any('/cancel', function (Request $request) {
        $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');
        if ($orderId) {
            $payment = Payment::find($orderId);
            if (!Auth::check() && $payment && $payment->user_id) {
                Auth::loginUsingId($payment->user_id);
            }
            if ($payment && $payment->status === 'Pendiente') {
                $payment->update(['notes' => 'Cancelado por el usuario en pasarela.']);
            }
        }
        
        $dest = Auth::check() ? 'student.payments' : '/';
        return redirect($dest == '/' ? '/' : route($dest))->with('error', 'Operación cancelada.');
    })->name('cancel');
});


// ==============================================================================
// RUTAS DE APLICACIÓN GENERAL
// ==============================================================================

// Validación de certificados (Pública con middleware signed)
Route::get('/certificates/verify/{student}/{course}', [CertificatePdfController::class, 'verify'])
    ->name('certificates.verify')
    ->middleware('signed');

// Tests de sistema
Route::get('/test', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json(['status' => 'OK', 'db' => 'Connected']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'Error', 'message' => $e->getMessage()]);
    }
});

// Dashboard Routing
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->hasAnyRole(['Registro', 'Contabilidad', 'Caja'])) return redirect()->route('admin.dashboard');
        if ($user->hasRole('Estudiante')) return redirect()->route('student.dashboard');
        if ($user->hasRole('Profesor')) return redirect()->route('teacher.dashboard');
        if ($user->hasRole('Solicitante')) return redirect()->route('applicant.portal');
        return redirect()->route('applicant.portal');
    })->name('dashboard');

    // Portal Aspirante
    Route::get('/portal-aspirante', ApplicantDashboard::class)->name('applicant.portal');
    Route::get('/portal-aspirante/solicitud', AdmissionsRegister::class)->name('applicant.admission-form');

    // Perfil Común
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==============================================================================
// RUTAS POR ROL
// ==============================================================================

// ADMINISTRACIÓN
Route::middleware(['auth', 'role:Admin|Registro|Contabilidad|Caja'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardIndex::class)->name('dashboard');
    
    // Académico
    Route::get('/students', StudentsIndex::class)->name('students.index');
    Route::get('/students/profile/{student}', StudentProfileIndex::class)->name('students.profile');
    Route::get('/courses', CoursesIndex::class)->name('courses.index');
    Route::get('/careers', CareersIndex::class)->name('careers.index');
    Route::get('/careers/{career}/curriculum', CareersCurriculum::class)->name('careers.curriculum');
    Route::get('/careers/{career}/curriculum/pdf', [CurriculumPdfController::class, 'download'])->name('careers.curriculum.pdf');
    Route::get('/classrooms', ClassroomManagement::class)->name('classrooms.index');
    Route::get('/teachers', TeachersIndex::class)->name('teachers.index');
    Route::get('/teachers/profile/{teacher}', TeacherProfileIndex::class)->name('teachers.profile');
    
    // Calendario & Admisiones
    if (class_exists(CalendarIndex::class)) Route::get('/calendar', CalendarIndex::class)->name('calendar.index');
    if (class_exists(AdmissionsIndex::class)) Route::get('/admissions', AdmissionsIndex::class)->name('admissions.index');

    // Finanzas
    Route::get('/finance/dashboard', FinanceDashboard::class)->name('finance.dashboard');
    Route::get('/finance/payment-concepts', FinanceConcepts::class)->name('finance.concepts');

    // Utilidades / Admin
    Route::get('/requests', RequestsManagement::class)->name('requests');
    Route::get('/import', DatabaseImport::class)->name('import');
    Route::get('/reports', ReportsIndex::class)->name('reports.index'); // Ojo: name conflict resolved in imports
    Route::get('/email-tester', EmailTester::class)->name('email-tester');
    Route::get('/users', AdminUsersIndex::class)->name('users.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    // Certificados
    Route::get('/certificates', CertificatesIndex::class)->name('certificates.index');
    Route::get('/certificate-templates', CertificateTemplatesIndex::class)->name('certificates.templates');
    Route::get('/certificate-editor/{templateId?}', CertificateEditor::class)->name('certificates.editor');
});

// ESTUDIANTES
Route::middleware(['auth', 'role:Estudiante'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentPortalDashboard::class)->name('dashboard');
    Route::get('/course/{enrollmentId}', StudentPortalCourseDetail::class)->name('course.detail');
    Route::get('/requests', StudentPortalRequests::class)->name('requests');
    Route::get('/payments', StudentPortalPayments::class)->name('payments');
    Route::get('/selection', StudentPortalSelection::class)->name('selection');
});

// PROFESORES
Route::middleware(['auth', 'role:Profesor|Admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherPortalDashboard::class)->name('dashboard');
    Route::get('/grades/{section}', TeacherPortalGrades::class)->name('grades');
    Route::get('/attendance/{section}', TeacherPortalAttendance::class)->name('attendance');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

// REPORTES & PDFS
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/student-report/{student}', [ReportController::class, 'generateStudentReport'])->name('reports.student-report');
    Route::get('/reports/attendance-report/{section}', [ReportController::class, 'generateAttendanceReport'])->name('reports.attendance-report');
    
    Route::get('/reports/attendance/{section}/pdf', [AttendancePdfController::class, 'download'])->name('reports.attendance.pdf');
    Route::get('/reports/grades/{section}/pdf', [GradesPdfController::class, 'download'])->name('reports.grades.pdf');
    Route::get('/reports/financial/pdf', [FinancialPdfController::class, 'download'])->name('reports.financial.pdf');
    Route::get('/reports/students-list/{section}/pdf', [StudentListPdfController::class, 'download'])->name('reports.students.pdf');
    Route::get('/reports/financial/{student}', [FinancialPdfController::class, 'download'])->name('reports.financial-report');
    Route::get('/reports/certificate/{student}/{course}/pdf', [CertificatePdfController::class, 'download'])->name('certificates.download');
    Route::get('/finance/ticket/{payment}', [FinancialPdfController::class, 'ticket'])->name('finance.ticket');
});

// CAMBIO CONTRASEÑA OBLIGATORIO
Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', [ForcePasswordChangeController::class, 'show'])->name('password.force_change');
    Route::post('/force-password-change', [ForcePasswordChangeController::class, 'update'])->name('password.force_update');
});

require __DIR__.'/auth.php';