<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendancePdfController; 
use App\Http\Controllers\GradesPdfController; 
use App\Http\Controllers\FinancialPdfController;
use App\Http\Controllers\StudentListPdfController; 
use App\Http\Controllers\CertificatePdfController; 
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\DatabaseImport; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
// Importamos componentes existentes
use App\Livewire\Admin\CertificateEditor;
use App\Livewire\Admin\CertificateTemplatesIndex;
use App\Livewire\Admin\ClassroomManagement;
// Importamos el componente financiero
use App\Livewire\Admin\FinanceDashboard;
// Importamos el probador de correos
use App\Livewire\Admin\EmailTester;
// Importar el nuevo controlador para PDF de Pensum
use App\Http\Controllers\CurriculumPdfController;

use App\Livewire\StudentPortal\Dashboard as StudentPortalDashboard;
use App\Livewire\StudentPortal\CourseDetail as StudentPortalCourseDetail;
use App\Livewire\StudentPortal\Requests as StudentPortalRequests;
use App\Livewire\StudentPortal\MyPayments as StudentPortalPayments; 
// ===> NUEVO: IMPORTAR COMPONENTE DE SELECCIÓN <===
use App\Livewire\StudentPortal\SubjectSelection as StudentPortalSelection;

use App\Livewire\TeacherPortal\Dashboard as TeacherPortalDashboard;
use App\Livewire\TeacherPortal\Grades as TeacherPortalGrades;
use App\Livewire\TeacherPortal\Attendance as TeacherPortalAttendance;

use App\Livewire\Admin\RequestsManagement;
use Illuminate\Support\Facades\URL; 
use Illuminate\Http\Request;
use App\Services\EcfService;
use App\Services\MatriculaService;
use App\Models\Payment;
use Illuminate\Support\Facades\Log; // Importante para el debug
use App\Models\User; // Importante para la recuperación de sesión

// === IMPORTACIONES NECESARIAS PARA ENVIAR CORREOS EN CARDNET ===
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReceiptMail;
use Barryvdh\DomPDF\Facade\Pdf;

// --- NUEVOS IMPORTS PARA ADMISIONES ---
use App\Livewire\Admissions\Index as AdmissionsIndex;
use App\Livewire\Admissions\Register as AdmissionsRegister;
use App\Livewire\Applicant\Dashboard as ApplicantDashboard; // Nuevo componente Portal Aspirante

// Importamos el componente de Calendario
use App\Livewire\Calendar\Index as CalendarIndex;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

// --- LINK DE REGISTRO PARA ESTUDIANTES (ASPIRANTES - CUENTA DE USUARIO) ---
// Este crea el usuario en el sistema
Route::get('/registro-estudiantes', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('student.register.link');

Route::post('/registro-estudiantes', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('student.register.store');


// ==============================================================================
// RUTAS DE CARDNET (DEFINIDAS EXPLÍCITAMENTE COMO PÚBLICAS Y SIN CSRF)
// ==============================================================================

// 1. Ruta de Respuesta (Éxito/Fallo) - Acepta POST y GET
Route::any('/cardnet/response', function (Request $request, EcfService $ecfService, MatriculaService $matriculaService) {
    
    // DEBUG: Verificar qué llega exactamente
    Log::info('Cardnet Debug: Retorno recibido en /cardnet/response', $request->all());

    $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');
    $responseCode = $request->input('ResponseCode');
    $authCode = $request->input('AuthorizationCode');
    $txId = $request->input('TransactionId');
    
    // Capturar mensaje de respuesta del banco si existe
    $responseMessage = $request->input('ResponseMessage') ?? $request->input('ResponseMsg') ?? 'Transacción declinada sin mensaje específico';

    // 1. Buscar el pago asociado
    $payment = Payment::find($orderId);

    if (!$payment) {
        Log::error('Cardnet Error: Pago no encontrado ID: ' . $orderId);
        return redirect('/')->with('error', 'Error crítico: Pago no encontrado o sesión expirada.');
    }

    // 2. AUTO-LOGIN DE EMERGENCIA
    // Si la sesión se perdió al volver del banco (Auth::check() es false), 
    // forzamos el login usando el ID del usuario dueño del pago.
    if (!Auth::check() && $payment->user_id) {
        Log::warning("Cardnet Debug: Sesión perdida detectada. Restaurando usuario ID {$payment->user_id}...");
        Auth::loginUsingId($payment->user_id);
        
        if(Auth::check()) {
             Log::info("Cardnet Debug: Sesión restaurada con éxito.");
        } else {
             Log::error("Cardnet Debug: Fallo al restaurar sesión.");
        }
    }

    // 3. Procesar Resultado de la Transacción
    if ($responseCode === '00') {
        // APROBADO
        $payment->update([
            'status' => 'Completado',
            'transaction_id' => $authCode,
            'notes' => "Aprobado Cardnet | Ref: {$txId} | Auth: {$authCode}",
        ]);

        // Lógica de Negocio (Factura, Matrícula, etc.)
        try {
            $ecfService->emitirComprobante($payment);
            
            if ($payment->enrollment) {
                $payment->enrollment->status = 'Cursando';
                $payment->enrollment->save();
            }
            
            $student = $payment->student;
            if ($student && !$student->student_code && $payment->paymentConcept && stripos($payment->paymentConcept->name, 'Inscripción') !== false) {
                $matriculaService->generarMatricula($payment);
            }

            // --- NUEVO: ENVIAR CORREO DE RECIBO AL ESTUDIANTE ---
            if ($student && $student->email) {
                try {
                    // Cargar relaciones necesarias para el PDF
                    $payment->load('student', 'paymentConcept', 'enrollment.courseSchedule.module');
                    
                    // Generar PDF y convertir a Base64
                    $pdfOutput = Pdf::loadView('reports.thermal-invoice', ['payment' => $payment])->output();
                    $pdfBase64 = base64_encode($pdfOutput);
                    
                    // Enviar correo
                    Mail::to($student->email)->send(new PaymentReceiptMail($payment, $pdfBase64));
                    
                    Log::info("Cardnet: Correo de recibo enviado a {$student->email}");
                } catch (\Exception $e) {
                    Log::error("Cardnet Error enviando correo: " . $e->getMessage());
                }
            }
            // -----------------------------------------------------

        } catch (\Exception $e) {
            Log::error("Cardnet Error post-proceso: " . $e->getMessage());
        }

        // Redirección según rol (ahora que la sesión debería estar activa)
        $user = Auth::user();
        if ($user && $user->hasRole('Estudiante')) {
            return redirect()->route('student.payments')->with('message', '¡Pago realizado con éxito! Código: ' . $authCode);
        } else {
            return redirect('/dashboard')->with('message', 'Pago procesado correctamente.');
        }

    } else {
        // RECHAZADO
        $payment->update([
            'status' => 'Pendiente', 
            'notes' => "Intento fallido Cardnet [{$responseCode}]: {$responseMessage}",
        ]);
        
        Log::warning("Cardnet Rechazo: Orden {$orderId} - Código {$responseCode} - Msg: {$responseMessage}");

        return redirect()->route('student.payments')->with('error', "El pago fue rechazado por el banco. Razón: {$responseMessage} (Código: {$responseCode})");
    }

})->name('cardnet.response')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]); 

// 2. Ruta de Cancelación
Route::any('/cardnet/cancel', function (Request $request) {
    Log::info('Cardnet Debug: Cancelación detectada', $request->all());
    
    $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');
    
    if ($orderId) {
        $payment = Payment::find($orderId);
        
        // Intentar restaurar sesión si se perdió al cancelar
        if (!Auth::check() && $payment && $payment->user_id) {
            Auth::loginUsingId($payment->user_id);
        }
        
        // Si cancela, también lo dejamos en Pendiente para que pueda intentar de nuevo
        if ($payment && $payment->status === 'Pendiente') {
            $payment->update([
                'status' => 'Pendiente',
                'notes' => 'Último intento cancelado por el usuario en la pasarela.'
            ]);
        }
    }
    
    if (Auth::check()) {
        return redirect()->route('student.payments')->with('error', 'Operación cancelada por el usuario. Puede intentar nuevamente.');
    } else {
        return redirect('/')->with('error', 'Operación cancelada.');
    }

})->name('cardnet.cancel')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


// ==============================================================================
// RUTAS DE APLICACIÓN ESTÁNDAR
// ==============================================================================

// --- RUTA PÚBLICA DE VALIDACIÓN DE CERTIFICADOS (QR) ---
Route::get('/certificates/verify/{student}/{course}', [CertificatePdfController::class, 'verify'])
    ->name('certificates.verify')
    ->middleware('signed');

// --- INICIO: RUTA DE PRUEBA (Health Check) ---
Route::get('/test', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbStatus = 'Conectado a ' . \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        $dbStatus = 'Error de conexión: ' . $e->getMessage();
    }

    return response()->json([
        'status' => 'OK',
        'message' => '¡Conexión exitosa! Laravel en cPanel está vivo.',
        'server_time' => now()->toDateTimeString(),
        'database_status' => $dbStatus,
        'php_version' => phpversion(),
    ]);
});

// --- NUEVA RUTA DE DIAGNÓSTICO WP API ---
Route::get('/test-wp', function () {
    $baseUri = config('services.wordpress.base_uri') ?? env('WP_API_BASE_URI');
    $secret = config('services.wordpress.secret') ?? env('WP_API_SECRET');
    $endpoint = 'sga/v1/get-courses/';
    $fullUrl = rtrim($baseUri, '/') . '/' . ltrim($endpoint, '/');

    $startTime = microtime(true);
    try {
        $response = Http::withoutVerifying()
            ->timeout(60)
            ->withHeaders([
                'X-SGA-Signature' => $secret,
                'Accept' => 'application/json',
                'User-Agent' => 'Laravel-Debug/1.0'
            ])
            ->get($fullUrl);
            
        $duration = microtime(true) - $startTime;

        return response()->json([
            'test' => 'Conexión Directa WP API',
            'url_intentada' => $fullUrl,
            'credenciales' => [
                'base_uri_configurado' => $baseUri,
                'tiene_secret' => !empty($secret) ? 'SÍ' : 'NO',
            ],
            'resultado' => [
                'http_status' => $response->status(),
                'exito_laravel' => $response->successful(),
                'duracion' => round($duration, 2) . 's',
                'body_preview' => Str::limit($response->body(), 500),
                'json_decodificado' => $response->json(),
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'test' => 'FALLO CRÍTICO',
            'error_tipo' => get_class($e),
            'mensaje' => $e->getMessage(),
            'url_intentada' => $fullUrl
        ], 500);
    }
});

// Ruta de 'dashboard' genérica que redirige según el rol
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user->hasRole('Admin') || $user->hasAnyRole(['Registro', 'Contabilidad', 'Caja'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Estudiante')) {
            return redirect()->route('student.dashboard');
        } elseif ($user->hasRole('Profesor')) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->hasRole('Solicitante')) {
            return redirect()->route('applicant.portal');
        }

        // Default si no tiene rol o es algo no contemplado
        return redirect()->route('applicant.portal');

    })->name('dashboard');

    // --- PORTAL DEL ASPIRANTE/SOLICITANTE ---
    // Aquí es donde el usuario 'Solicitante' completa su solicitud y ve el estado
    Route::get('/portal-aspirante', ApplicantDashboard::class)->name('applicant.portal');
    
    // === NUEVA RUTA: FORMULARIO DE ADMISIÓN DENTRO DEL PORTAL ===
    Route::get('/portal-aspirante/solicitud', AdmissionsRegister::class)->name('applicant.admission-form');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// --- RUTAS DE ADMINISTRADOR ---
Route::middleware(['auth', 'role:Admin|Registro|Contabilidad|Caja'])->prefix('admin')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('admin.dashboard');
    Route::get('/students', \App\Livewire\Students\Index::class)->name('admin.students.index');
    Route::get('/students/profile/{student}', \App\Livewire\StudentProfile\Index::class)->name('admin.students.profile');
    
    Route::get('/courses', \App\Livewire\Courses\Index::class)->name('admin.courses.index');
    
    Route::get('/careers', \App\Livewire\Careers\Index::class)->name('admin.careers.index');
    Route::get('/careers/{career}/curriculum', \App\Livewire\Careers\Curriculum::class)->name('admin.careers.curriculum');
    Route::get('/careers/{career}/curriculum/pdf', [CurriculumPdfController::class, 'download'])->name('admin.careers.curriculum.pdf');
    
    if (class_exists(CalendarIndex::class)) {
        Route::get('/calendar', CalendarIndex::class)->name('admin.calendar.index');
    }
    
    // --- GESTIÓN DE ADMISIONES ---
    if (class_exists(AdmissionsIndex::class)) {
        Route::get('/admissions', AdmissionsIndex::class)->name('admin.admissions.index');
    }

    Route::get('/finance/dashboard', FinanceDashboard::class)->name('admin.finance.dashboard');
    Route::get('/finance/payment-concepts', \App\Livewire\Finance\PaymentConcepts::class)->name('admin.finance.concepts');

    Route::get('/teachers', \App\Livewire\Teachers\Index::class)->name('admin.teachers.index');
    Route::get('/teachers/profile/{teacher}', \App\Livewire\TeacherProfile\Index::class)->name('admin.teachers.profile');

    Route::get('/requests', \App\Livewire\Admin\RequestsManagement::class)->name('admin.requests');
    Route::get('/import', DatabaseImport::class)->name('admin.import');
    
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
    Route::get('/certificates', \App\Livewire\Certificates\Index::class)->name('admin.certificates.index'); 
    
    Route::get('/certificate-templates', CertificateTemplatesIndex::class)->name('admin.certificates.templates');
    Route::get('/certificate-editor', CertificateEditor::class)->name('admin.certificates.editor');
    Route::get('/certificate-editor/{templateId?}', CertificateEditor::class)->name('admin.certificates.edit');

    Route::get('/classrooms', ClassroomManagement::class)->name('admin.classrooms.index');

    Route::get('/email-tester', EmailTester::class)->name('admin.email-tester');

    Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('admin.users.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
});

// --- RUTAS DE ESTUDIANTE ---
Route::middleware(['auth', 'role:Estudiante'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', \App\Livewire\StudentPortal\Dashboard::class)->name('dashboard');
    Route::get('/course/{enrollmentId}', \App\Livewire\StudentPortal\CourseDetail::class)->name('course.detail');
    Route::get('/requests', \App\Livewire\StudentPortal\Requests::class)->name('requests');
    Route::get('/payments', StudentPortalPayments::class)->name('payments');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // ===> RUTA PARA SELECCIÓN DE MATERIAS <===
    Route::get('/selection', StudentPortalSelection::class)->name('selection');
});

// --- RUTAS DE PROFESOR ---
Route::middleware(['auth', 'role:Profesor|Admin'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', \App\Livewire\TeacherPortal\Dashboard::class)->name('teacher.dashboard');
    Route::get('/grades/{section}', \App\Livewire\TeacherPortal\Grades::class)->name('teacher.grades');
    Route::get('/attendance/{section}', \App\Livewire\TeacherPortal\Attendance::class)->name('teacher.attendance');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('teacher.profile.edit');
});

// --- RUTAS DE REPORTES (Generación PDF) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/student-report/{student}', [ReportController::class, 'generateStudentReport'])->name('reports.student-report');
    Route::get('/reports/attendance-report/{section}', [ReportController::class, 'generateAttendanceReport'])->name('reports.attendance-report');
    
    Route::get('/reports/attendance/{section}/pdf', [AttendancePdfController::class, 'download'])->name('reports.attendance.pdf');
    Route::get('/reports/grades/{section}/pdf', [GradesPdfController::class, 'download'])->name('reports.grades.pdf');
    Route::get('/reports/financial/pdf', [FinancialPdfController::class, 'download'])->name('reports.financial.pdf');
    Route::get('/reports/students-list/{section}/pdf', [StudentListPdfController::class, 'download'])->name('reports.students.pdf');
    Route::get('/reports/financial/{student}', [FinancialPdfController::class, 'download'])->name('reports.financial-report');

    Route::get('/reports/certificate/{student}/{course}/pdf', [CertificatePdfController::class, 'download'])->name('certificates.download'); 
    
    Route::get('/finance/ticket/{payment}', [\App\Http\Controllers\FinancialPdfController::class, 'ticket'])->name('finance.ticket');
});

// --- RUTAS PARA CAMBIO DE CONTRASEÑA OBLIGATORIO ---
Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])
        ->name('password.force_change');
    Route::post('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])
        ->name('password.force_update');
});

require __DIR__.'/auth.php';