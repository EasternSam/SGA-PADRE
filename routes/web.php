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

use App\Livewire\StudentPortal\Dashboard as StudentPortalDashboard;
use App\Livewire\StudentPortal\CourseDetail as StudentPortalCourseDetail;
use App\Livewire\StudentPortal\Requests as StudentPortalRequests;
use App\Livewire\StudentPortal\MyPayments as StudentPortalPayments; 

use App\Livewire\TeacherPortal\Dashboard as TeacherPortalDashboard;
use App\Livewire\TeacherPortal\Grades as TeacherPortalGrades;
use App\Livewire\TeacherPortal\Attendance as TeacherPortalAttendance;

use App\Livewire\Admin\RequestsManagement;
use Illuminate\Support\Facades\URL; 
use Illuminate\Http\Request;
use App\Services\EcfService;
use App\Services\MatriculaService;
use App\Models\Payment;
use Illuminate\Support\Facades\Log; // Asegúrate de importar Log

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

// --- RUTAS DE CALLBACK CARDNET (Públicas y sin CSRF) ---

// 1. Ruta de Respuesta (Éxito/Fallo) - Acepta POST y GET
Route::any('/cardnet/response', function (Request $request, EcfService $ecfService, MatriculaService $matriculaService) {
    
    // DEBUG: Ver qué llega exactamente de Cardnet
    Log::info('Cardnet DEBUG: Datos recibidos en Response', $request->all());

    // 1. Recuperar IDs clave
    // Cardnet a veces usa "OrdenID" y a veces "OrdenId", verificamos ambos
    $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');
    $responseCode = $request->input('ResponseCode');
    $authCode = $request->input('AuthorizationCode');
    $txId = $request->input('TransactionId') ?? $request->input('TransactionID');

    if (!$orderId) {
        Log::error('Cardnet Error: No llegó OrdenId en la respuesta.');
        return redirect('/')->with('error', 'Error crítico: La pasarela no devolvió el número de orden.');
    }

    // 2. Buscar el pago
    $payment = Payment::find($orderId);
    if (!$payment) {
        Log::error("Cardnet Error: Pago ID {$orderId} no encontrado en BD.");
        return redirect('/')->with('error', 'Error: Referencia de pago no encontrada en el sistema.');
    }

    // 3. AUTO-LOGIN: Recuperar sesión si se perdió
    if (!Auth::check()) {
        if ($payment->user_id) {
            Log::warning("Cardnet DEBUG: Sesión perdida. Restaurando usuario ID {$payment->user_id}...");
            Auth::loginUsingId($payment->user_id);
            Log::info("Cardnet DEBUG: Usuario {$payment->user_id} logueado exitosamente.");
        } else {
            Log::error("Cardnet Error: El pago {$payment->id} no tiene user_id asociado. No se puede restaurar sesión.");
        }
    }

    // 4. Procesar Resultado
    if ($responseCode === '00') {
        // APROBADO
        $payment->update([
            'status' => 'Completado',
            'transaction_id' => $authCode,
            'notes' => "Cardnet Aprobado | Auth: {$authCode} | Ref: {$txId}",
        ]);

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

        } catch (\Exception $e) {
            Log::error("Error post-pago Cardnet: " . $e->getMessage());
        }

        return redirect()->route('student.payments')->with('message', '¡Pago procesado correctamente! Código: ' . $authCode);

    } else {
        // RECHAZADO
        $payment->update([
            'status' => 'Rechazado',
            'notes' => "Cardnet Rechazo Código: {$responseCode} - " . ($request->input('ResponseMessage') ?? ''),
        ]);

        return redirect()->route('student.payments')->with('error', 'Transacción rechazada por el banco. Código: ' . $responseCode);
    }
})->name('cardnet.response')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]); 

// 2. Ruta de Cancelación (NUEVA: Para evitar error 405)
Route::any('/cardnet/cancel', function (Request $request) {
    Log::info('Cardnet DEBUG: Cancelación detectada', $request->all());
    
    $orderId = $request->input('OrdenId') ?? $request->input('OrdenID');
    
    if ($orderId) {
        $payment = Payment::find($orderId);
        if ($payment) {
            // Restaurar sesión si es necesario
            if (!Auth::check() && $payment->user_id) {
                Auth::loginUsingId($payment->user_id);
            }

            if ($payment->status === 'Pendiente') {
                $payment->update(['status' => 'Cancelado', 'notes' => 'Usuario canceló en la pasarela.']);
            }
        }
    }
    
    // Si no pudimos recuperar sesión, ir al login, si no, a pagos
    if (Auth::check()) {
        return redirect()->route('student.payments')->with('error', 'Operación cancelada por el usuario.');
    } else {
        return redirect('/')->with('error', 'Operación cancelada. Por favor inicie sesión nuevamente.');
    }

})->name('cardnet.cancel')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


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

        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Estudiante')) {
            return redirect()->route('student.dashboard');
        } elseif ($user->hasRole('Profesor')) {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route('profile.edit');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// --- RUTAS DE ADMINISTRADOR ---
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('admin.dashboard');
    Route::get('/students', \App\Livewire\Students\Index::class)->name('admin.students.index');
    Route::get('/students/profile/{student}', \App\Livewire\StudentProfile\Index::class)->name('admin.students.profile');
    Route::get('/courses', \App\Livewire\Courses\Index::class)->name('admin.courses.index');
    
    // --- GESTIÓN FINANCIERA ---
    // Dashboard General de Finanzas
    Route::get('/finance/dashboard', FinanceDashboard::class)->name('admin.finance.dashboard');
    // Configuración de Conceptos
    Route::get('/finance/payment-concepts', \App\Livewire\Finance\PaymentConcepts::class)->name('admin.finance.concepts');

    Route::get('/teachers', \App\Livewire\Teachers\Index::class)->name('admin.teachers.index');
    Route::get('/teachers/profile/{teacher}', \App\Livewire\TeacherProfile\Index::class)->name('admin.teachers.profile');

    Route::get('/requests', \App\Livewire\Admin\RequestsManagement::class)->name('admin.requests');
    Route::get('/import', DatabaseImport::class)->name('admin.import');
    
    // --- GESTIÓN DE REPORTES Y CERTIFICADOS (Admin) ---
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
    Route::get('/certificates', \App\Livewire\Certificates\Index::class)->name('admin.certificates.index'); 
    
    // --- GESTIÓN DE PLANTILLAS DE DIPLOMAS ---
    // Listado de plantillas
    Route::get('/certificate-templates', CertificateTemplatesIndex::class)->name('admin.certificates.templates');
    
    // Editor (Crear nueva)
    Route::get('/certificate-editor', CertificateEditor::class)->name('admin.certificates.editor');
    
    // Editor (Editar existente - sobreescribimos la ruta anterior genérica para ser específicos)
    Route::get('/certificate-editor/{templateId?}', CertificateEditor::class)->name('admin.certificates.edit');

    // --- GESTIÓN DE AULAS ---
    Route::get('/classrooms', ClassroomManagement::class)->name('admin.classrooms.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
});

// --- RUTAS DE ESTUDIANTE ---
Route::middleware(['auth', 'role:Estudiante'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', \App\Livewire\StudentPortal\Dashboard::class)->name('dashboard');
    Route::get('/course/{enrollmentId}', \App\Livewire\StudentPortal\CourseDetail::class)->name('course.detail');
    Route::get('/requests', \App\Livewire\StudentPortal\Requests::class)->name('requests');
    Route::get('/payments', StudentPortalPayments::class)->name('payments');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
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

    // --- RUTA DESCARGA CERTIFICADO ---
    Route::get('/reports/certificate/{student}/{course}/pdf', [CertificatePdfController::class, 'download'])->name('certificates.download'); 
    
    // --- RUTA TICKET TÉRMICO (NUEVO) ---
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