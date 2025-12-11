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
use Illuminate\Support\Facades\Http; // <-- Añadido
use Illuminate\Support\Str; // <-- Añadido

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login'); // Tu ruta raíz original
});

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

// --- RUTA DE DIAGNÓSTICO WP API ---
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

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard Genérico con Redirección
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user->hasRole('Admin') || $user->hasRole('Administrador')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Estudiante')) {
            return redirect()->route('student.dashboard');
        } elseif ($user->hasRole('Profesor')) {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route('profile.edit');
    })->name('dashboard');

    // --- Rutas de Administración ---
    Route::middleware(['role:Administrador|Secretaria|Admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', DashboardIndex::class)->name('admin.dashboard');
        Route::get('/students', StudentsIndex::class)->name('admin.students.index'); // Ojo con el nombre de ruta duplicado si usas students.index arriba
        Route::get('/students/profile/{student}', StudentProfileIndex::class)->name('admin.students.profile');
        
        Route::get('/courses', CoursesIndex::class)->name('admin.courses.index');
        Route::get('/finance/payment-concepts', \App\Livewire\Finance\PaymentConcepts::class)->name('admin.finance.concepts');
        
        Route::get('/teachers', TeachersIndex::class)->name('admin.teachers.index');
        Route::get('/teachers/profile/{user}', TeacherProfileIndex::class)->name('admin.teachers.profile');
        
        Route::get('/reports', ReportsIndex::class)->name('reports.index');
        Route::get('/database-import', DatabaseImport::class)->name('admin.database-import');
        Route::get('/requests', RequestsManagement::class)->name('admin.requests');
        Route::get('/import', DatabaseImport::class)->name('admin.import'); // Mantener la ruta original 'import' si se usa
        
        // Perfil Admin
        Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    });
    
    // --- RUTAS DE REPORTES PDF ---
    // NOTA: Estas rutas deben estar fuera del grupo 'admin' si el estudiante las va a usar, 
    // o deben tener permisos específicos. Aquí las dejo accesibles para 'auth' general, 
    // pero idealmente deberías protegerlas con policies en el controlador.
    
    Route::get('/reports/student/{student}', [ReportController::class, 'generateStudentReport'])->name('reports.student-report');
    Route::get('/reports/attendance-report/{section}', [ReportController::class, 'generateAttendanceReport'])->name('reports.attendance-report');
    
    Route::get('/reports/student-list/{courseSchedule}', [StudentListPdfController::class, 'generate'])->name('reports.student-list-pdf');
    
    // --- RUTAS PDF (CORREGIDAS Y AÑADIDAS) ---
    Route::get('/reports/attendance/{courseSchedule}/pdf', [AttendancePdfController::class, 'download'])->name('reports.attendance.pdf');
    Route::get('/reports/grades/{courseSchedule}/pdf', [GradesPdfController::class, 'download'])->name('reports.grades.pdf');
    
    // Ruta antigua de PDF Financiero (general)
    Route::get('/reports/financial/pdf', [FinancialPdfController::class, 'download'])->name('reports.financial.pdf');
    
    // --> NUEVA RUTA PARA ESTUDIANTES (ESTADO DE CUENTA INDIVIDUAL) <--
    // Apunta al mismo método 'download', el controlador detectará el parámetro {student}
    Route::get('/reports/financial/{student}', [FinancialPdfController::class, 'download'])->name('reports.financial-report');
    
    // PDF Lista Estudiantes
    Route::get('/reports/students-list/{section}/pdf', [StudentListPdfController::class, 'download'])->name('reports.students.pdf');


    // --- Rutas del Portal Estudiante ---
    Route::middleware(['role:Estudiante'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', StudentDashboard::class)->name('dashboard');
        Route::get('/course/{enrollmentId}', StudentCourseDetail::class)->name('course-detail');
        Route::get('/payments', StudentMyPayments::class)->name('payments');
        Route::get('/requests', StudentRequests::class)->name('requests');
        // Perfil Estudiante
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    });

    // --- Rutas del Portal Profesor ---
    Route::middleware(['role:Profesor|Admin'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');
        Route::get('/attendance/{scheduleId}', TeacherAttendance::class)->name('attendance');
        Route::get('/grades/{scheduleId}', TeacherGrades::class)->name('grades');
         // Perfil Profesor
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    });

    // Rutas de Perfil Genéricas
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // --- RUTAS PARA CAMBIO DE CONTRASEÑA OBLIGATORIO ---
    Route::get('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])
        ->name('password.force_change');
    Route::post('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])
        ->name('password.force_update');
});

require __DIR__.'/auth.php';