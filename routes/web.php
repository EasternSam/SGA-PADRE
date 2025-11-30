<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendancePdfController; // Importación añadida
use Illuminate\Support\Facades\Auth;
use App\Livewire\Admin\DatabaseImport; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
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
    
    // --- NUEVO MÓDULO DE REPORTES ---
    // Accesible para usuarios autenticados (se pueden añadir roles específicos si se desea)
    Route::get('/reports', \App\Livewire\Reports\Index::class)->name('reports.index');
});


// --- RUTAS DE ADMINISTRADOR ---
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('admin.dashboard');
    Route::get('/students', \App\Livewire\Students\Index::class)->name('admin.students.index');
    Route::get('/students/profile/{student}', \App\Livewire\StudentProfile\Index::class)->name('admin.students.profile');
    Route::get('/courses', \App\Livewire\Courses\Index::class)->name('admin.courses.index');
    Route::get('/finance/payment-concepts', \App\Livewire\Finance\PaymentConcepts::class)->name('admin.finance.concepts');

    Route::get('/teachers', \App\Livewire\Teachers\Index::class)->name('admin.teachers.index');
    Route::get('/teachers/profile/{teacher}', \App\Livewire\TeacherProfile\Index::class)->name('admin.teachers.profile');

    Route::get('/requests', \App\Livewire\Admin\RequestsManagement::class)->name('admin.requests');
    Route::get('/import', DatabaseImport::class)->name('admin.import');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
});

// --- RUTAS DE ESTUDIANTE ---
Route::middleware(['auth', 'role:Estudiante'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', \App\Livewire\StudentPortal\Dashboard::class)->name('dashboard');
    Route::get('/course/{enrollmentId}', \App\Livewire\StudentPortal\CourseDetail::class)->name('course.detail');
    Route::get('/requests', \App\Livewire\StudentPortal\Requests::class)->name('requests');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

// --- RUTAS DE PROFESOR ---
Route::middleware(['auth', 'role:Profesor|Admin'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', \App\Livewire\TeacherPortal\Dashboard::class)->name('teacher.dashboard');
    Route::get('/grades/{section}', \App\Livewire\TeacherPortal\Grades::class)->name('teacher.grades');
    Route::get('/attendance/{section}', \App\Livewire\TeacherPortal\Attendance::class)->name('teacher.attendance');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('teacher.profile.edit');
});

// --- RUTAS DE REPORTES ---
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/student-report/{student}', [ReportController::class, 'generateStudentReport'])->name('reports.student-report');
    Route::get('/reports/attendance-report/{section}', [ReportController::class, 'generateAttendanceReport'])->name('reports.attendance-report');
    
    // --- RUTA PDF DE ASISTENCIA ---
    Route::get('/reports/attendance/{section}/pdf', [AttendancePdfController::class, 'download'])->name('reports.attendance.pdf');
});

// --- RUTAS PARA CAMBIO DE CONTRASEÑA OBLIGATORIO ---
Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])
        ->name('password.force_change');
    Route::post('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])
        ->name('password.force_update');
});

require __DIR__.'/auth.php';