<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\EcfService;
use App\Services\MatriculaService;
use Illuminate\Support\Facades\Log;

// ... (El resto de tus imports) ...

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

// --- RUTA DE RETORNO CARDNET (Redirección POST) ---
// Debe aceptar POST porque Cardnet envía datos, y GET por si acaso.
Route::any('/cardnet/response', function (Request $request, EcfService $ecfService, MatriculaService $matriculaService) {
    
    // 1. Obtener parámetros de Cardnet
    $responseCode = $request->input('ResponseCode');
    $orderId = $request->input('OrdenId'); // El ID de pago que enviamos
    $authCode = $request->input('AuthorizationCode');
    $txId = $request->input('TransactionId'); // ID de Cardnet
    
    Log::info("Cardnet Response Recibida: Orden {$orderId}, Código {$responseCode}");

    // 2. Buscar el pago pendiente
    $payment = Payment::find($orderId);

    if (!$payment) {
        return redirect()->route('dashboard')->with('error', 'Error: Pago no encontrado o sesión expirada.');
    }

    // 3. Verificar Resultado
    if ($responseCode === '00') {
        // PAGO APROBADO
        $payment->update([
            'status' => 'Completado',
            'transaction_id' => $authCode, // Guardar código de autorización
            'notes' => "Cardnet ID: {$txId} | Aprobado",
        ]);

        // Lógica de negocio (Factura, Matrícula, etc.)
        try {
            // Generar Factura Electrónica
            $ecfService->emitirComprobante($payment);
            
            // Activar inscripción si aplica
            if ($payment->enrollment) {
                $payment->enrollment->status = 'Cursando';
                $payment->enrollment->save();
            }
            
            // Generar matrícula si es nuevo ingreso
            $student = $payment->student;
            if ($student && !$student->student_code && $payment->paymentConcept && stripos($payment->paymentConcept->name, 'Inscripción') !== false) {
                $matriculaService->generarMatricula($payment);
            }

        } catch (\Exception $e) {
            Log::error("Error post-pago Cardnet (ID {$payment->id}): " . $e->getMessage());
        }

        // Redirigir al dashboard con mensaje de éxito y abrir ticket si es posible
        // Nota: Abrir ticket en nueva pestaña desde redirección es difícil, 
        // mejor mostrar botón de "Imprimir Recibo" en el mensaje flash.
        return redirect()->route('dashboard')->with('message', 'Pago aprobado exitosamente. Código de autorización: ' . $authCode);

    } else {
        // PAGO RECHAZADO
        $payment->update([
            'status' => 'Rechazado',
            'notes' => "Cardnet Rechazo Código: {$responseCode}",
        ]);

        return redirect()->route('dashboard')->with('error', 'El pago fue rechazado por el banco. Código: ' . $responseCode);
    }

})->name('cardnet.response')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]); 
// IMPORTANTE: Excluimos CSRF porque Cardnet hace POST desde un dominio externo.

// ... (Resto de tus rutas originales: test, test-wp, dashboard, admin, etc.) ...
// Asegúrate de mantener todo el código original debajo de este bloque.

// --- RUTA PÚBLICA DE VALIDACIÓN DE CERTIFICADOS (QR) ---
Route::get('/certificates/verify/{student}/{course}', [CertificatePdfController::class, 'verify'])
    ->name('certificates.verify')
    ->middleware('signed');

// ... (Continuar con el resto de tu archivo web.php) ...
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
                'body_preview' => \Illuminate\Support\Str::limit($response->body(), 500),
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

Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('admin.dashboard');
    Route::get('/students', \App\Livewire\Students\Index::class)->name('admin.students.index');
    Route::get('/students/profile/{student}', \App\Livewire\StudentProfile\Index::class)->name('admin.students.profile');
    Route::get('/courses', \App\Livewire\Courses\Index::class)->name('admin.courses.index');
    Route::get('/finance/dashboard', \App\Livewire\Admin\FinanceDashboard::class)->name('admin.finance.dashboard');
    Route::get('/finance/payment-concepts', \App\Livewire\Finance\PaymentConcepts::class)->name('admin.finance.concepts');
    Route::get('/teachers', \App\Livewire\Teachers\Index::class)->name('admin.teachers.index');
    Route::get('/teachers/profile/{teacher}', \App\Livewire\TeacherProfile\Index::class)->name('admin.teachers.profile');
    Route::get('/requests', \App\Livewire\Admin\RequestsManagement::class)->name('admin.requests');
    Route::get('/import', \App\Livewire\Admin\DatabaseImport::class)->name('admin.import');
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
    Route::get('/certificates', \App\Livewire\Certificates\Index::class)->name('admin.certificates.index'); 
    Route::get('/certificate-templates', \App\Livewire\Admin\CertificateTemplatesIndex::class)->name('admin.certificates.templates');
    Route::get('/certificate-editor', \App\Livewire\Admin\CertificateEditor::class)->name('admin.certificates.editor');
    Route::get('/certificate-editor/{templateId?}', \App\Livewire\Admin\CertificateEditor::class)->name('admin.certificates.edit');
    Route::get('/classrooms', \App\Livewire\Admin\ClassroomManagement::class)->name('admin.classrooms.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
});

Route::middleware(['auth', 'role:Estudiante'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', \App\Livewire\StudentPortal\Dashboard::class)->name('dashboard');
    Route::get('/course/{enrollmentId}', \App\Livewire\StudentPortal\CourseDetail::class)->name('course.detail');
    Route::get('/requests', \App\Livewire\StudentPortal\Requests::class)->name('requests');
    Route::get('/payments', \App\Livewire\StudentPortal\MyPayments::class)->name('payments');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

Route::middleware(['auth', 'role:Profesor|Admin'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', \App\Livewire\TeacherPortal\Dashboard::class)->name('teacher.dashboard');
    Route::get('/grades/{section}', \App\Livewire\TeacherPortal\Grades::class)->name('teacher.grades');
    Route::get('/attendance/{section}', \App\Livewire\TeacherPortal\Attendance::class)->name('teacher.attendance');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('teacher.profile.edit');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/reports/student-report/{student}', [ReportController::class, 'generateStudentReport'])->name('reports.student-report');
    Route::get('/reports/attendance-report/{section}', [ReportController::class, 'generateAttendanceReport'])->name('reports.attendance-report');
    Route::get('/reports/attendance/{section}/pdf', [AttendancePdfController::class, 'download'])->name('reports.attendance.pdf');
    Route::get('/reports/grades/{section}/pdf', [GradesPdfController::class, 'download'])->name('reports.grades.pdf');
    Route::get('/reports/financial/pdf', [FinancialPdfController::class, 'download'])->name('reports.financial.pdf');
    Route::get('/reports/students-list/{section}/pdf', [StudentListPdfController::class, 'download'])->name('reports.students.pdf');
    Route::get('/reports/financial/{student}', [FinancialPdfController::class, 'download'])->name('reports.financial-report');
    Route::get('/reports/certificate/{student}/{course}/pdf', [CertificatePdfController::class, 'download'])->name('certificates.download'); 
    
    // --- RUTA TICKET TÉRMICO (NUEVO) ---
    Route::get('/finance/ticket/{payment}', [\App\Http\Controllers\FinancialPdfController::class, 'ticket'])->name('finance.ticket');
});

// --- RUTA DE RETORNO CARDNET (Sin protección CSRF porque viene de fuera) ---
Route::any('/cardnet/response', function (\Illuminate\Http\Request $request, \App\Services\EcfService $ecfService, \App\Services\MatriculaService $matriculaService) {
    $responseCode = $request->input('ResponseCode');
    $orderId = $request->input('OrdenId');
    $authCode = $request->input('AuthorizationCode');
    $txId = $request->input('TransactionId');

    $payment = \App\Models\Payment::find($orderId);

    if (!$payment) {
        return redirect()->route('dashboard')->with('error', 'Pago no encontrado.');
    }

    if ($responseCode === '00') { // Aprobado
        $payment->update([
            'status' => 'Completado',
            'transaction_id' => $authCode,
            'notes' => "Cardnet Aprobado. ID: {$txId}",
        ]);

        try {
            // Lógica de negocio (Factura, Matrícula, etc.)
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
            \Illuminate\Support\Facades\Log::error("Error post-pago: " . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('message', 'Pago aprobado exitosamente.');
    } else {
        $payment->update(['status' => 'Rechazado', 'notes' => "Rechazo: {$responseCode}"]);
        return redirect()->route('dashboard')->with('error', 'El pago fue rechazado por el banco.');
    }
})->name('cardnet.response')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// --- RUTAS PARA CAMBIO DE CONTRASEÑA OBLIGATORIO ---
Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])
        ->name('password.force_change');
    Route::post('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])
        ->name('password.force_update');
});

require __DIR__.'/auth.php';