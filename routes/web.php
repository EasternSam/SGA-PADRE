<?php

use App\Http\Controllers\AdmissionDocumentController;
use App\Http\Controllers\CertificatePdfController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Admissions\Register as AdmissionsRegister;
use App\Livewire\Applicant\Dashboard as ApplicantDashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rutas públicas, de diagnóstico, y compartidas. Las rutas específicas
| de cada módulo están en sus archivos correspondientes:
|
| - routes/admin.php     → Panel administrativo
| - routes/student.php   → Portal del estudiante
| - routes/teacher.php   → Portal del profesor
| - routes/kiosk.php     → Kiosco de autoservicio
| - routes/cardnet.php   → Callbacks de pago Cardnet
| - routes/reports.php   → Generación de PDFs y reportes
|
*/

// ==============================================================================
// RUTA DE DIAGNÓSTICO TOTAL (VITAL PARA DEBUG)
// ==============================================================================
Route::get('/system/debug-license', function () {
    $licenseKey = env('APP_LICENSE_KEY');
    $domain = request()->getHost();
    $masterUrl = rtrim(env('SAAS_MASTER_URL', 'https://gestion.90s.agency'), '/');

    try {
        $startTime = microtime(true);
        $response = Http::withoutVerifying()
            ->timeout(20)
            ->post("{$masterUrl}/api/v1/validate-license", [
                'license_key' => $licenseKey,
                'domain'      => $domain,
            ]);
        $duration = microtime(true) - $startTime;

        return response()->json([
            'TITULO' => 'DIAGNÓSTICO DE CONEXIÓN SAAS',
            '1. Configuración Local' => [
                'License Key' => $licenseKey,
                'Domain Detectado' => $domain,
                'Master URL' => $masterUrl,
            ],
            '2. Respuesta del Maestro' => [
                'HTTP Status' => $response->status(),
                'Es Exitosa (bool)' => $response->successful(),
                'Body JSON' => $response->json(),
            ],
            '3. Rendimiento' => [
                'Tiempo de respuesta' => round($duration, 2) . ' segundos',
            ],
            '4. Conclusión' => ($response->successful() && $response->json('status') === 'success')
                ? 'EL MAESTRO DICE QUE ESTÁ ACTIVO'
                : 'EL MAESTRO DICE QUE ESTÁ SUSPENDIDO O ERROR'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'ERROR CRÍTICO' => 'Falló la conexión con el maestro',
            'Mensaje' => $e->getMessage(),
        ], 500);
    }
});

// ==============================================================================
// RUTA DE UTILIDAD: FORZAR RE-VERIFICACIÓN DE LICENCIA
// ==============================================================================
Route::get('/system/refresh-license', function () {
    \Illuminate\Support\Facades\Cache::forget('saas_license_valid');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');

    return response()->json([
        'status' => 'ok',
        'message' => 'Caché de licencia eliminada. El sistema consultará al maestro en la próxima recarga.'
    ]);
});

// ==============================================================================
// RUTAS DEL INSTALADOR SAAS
// ==============================================================================
Route::get('/install', [InstallerController::class, 'index'])->name('installer.step1');
Route::post('/install', [InstallerController::class, 'install'])->name('installer.submit');

// --- RUTA DE AUDITORÍA FRONTEND (CAJA NEGRA) ---
Route::post('/api/log-click', function (Request $request) {
    $user = auth()->user() ? "ID:".auth()->id() : 'Guest';
    $data = json_decode($request->getContent(), true) ?? [];

    Log::channel('audit')->info("🖱️ CLIC DETECTADO ($user)", [
        'Elemento' => $data['tag'] ?? '?',
        'Texto' => $data['text'] ?? '',
        'Wire:Click' => $data['wire_click'] ?? '',
        'URL' => $data['url'] ?? '',
        'Classes' => $data['classes'] ?? ''
    ]);

    return response()->noContent();
});

// --- RUTA RAÍZ ---
Route::get('/', function () {
    return view('auth.login');
});

// --- LINK DE REGISTRO PARA ESTUDIANTES ---
Route::middleware(['feature:academic_careers', 'guest'])->group(function () {
    Route::get('/registro-estudiantes', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])
        ->name('student.register.link');
    Route::post('/registro-estudiantes', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store'])
        ->name('student.register.store');
});

// --- CERTIFICADOS PÚBLICOS ---
Route::middleware(['feature:reports_advanced', 'signed'])->group(function () {
    Route::get('/certificates/verify/{student}/{course}', [CertificatePdfController::class, 'verify'])
        ->name('certificates.verify');
});

// --- HEALTH CHECK ---
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

// --- DIAGNÓSTICO WP API ---
Route::middleware(['feature:api_access'])->group(function () {
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
            return response()->json(['test' => 'FALLO CRÍTICO', 'mensaje' => $e->getMessage()], 500);
        }
    });
});

// --- DIAGNÓSTICO MOODLE ---
Route::middleware(['feature:virtual_classroom'])->group(function () {
    Route::get('/test-moodle', function () {
        $url = config('services.moodle.url');
        $token = config('services.moodle.token');
        $endpoint = $url . '/webservice/rest/server.php';

        $params = [
            'wstoken' => $token,
            'wsfunction' => 'core_course_get_courses',
            'moodlewsrestformat' => 'json'
        ];

        $startTime = microtime(true);
        try {
            $response = Http::asForm()->post($endpoint, $params);
            $duration = microtime(true) - $startTime;
            $json = $response->json();
            $status = isset($json['exception']) ? 'ERROR MOODLE' : 'EXITO';

            return response()->json([
                'test' => 'Conexión Moodle API',
                'status' => $status,
                'resultado' => [
                    'http_status' => $response->status(),
                    'duracion' => round($duration, 2) . 's',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['test' => 'FALLO CRÍTICO', 'error' => $e->getMessage()], 500);
        }
    });
});

// ==============================================================================
// RUTAS AUTENTICADAS COMPARTIDAS
// ==============================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/kiosk/auth/qr/{token}', \App\Livewire\Mobile\QrAuthorization::class)->name('kiosk.auth.mobile');

    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user->hasRole('Admin') || $user->hasAnyRole(['Registro', 'Contabilidad', 'Caja'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Estudiante')) {
            return redirect()->route('student.dashboard');
        } elseif ($user->hasRole('Profesor')) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->hasRole('Solicitante')) {
            if (\App\Helpers\SaaS::showCareers()) {
                return redirect()->route('applicant.portal');
            } else {
                return abort(403, 'El portal de admisiones se encuentra deshabilitado.');
            }
        }
        return abort(403, 'Rol no autorizado.');
    })->name('dashboard');

    Route::middleware(['feature:academic_careers'])->group(function () {
        Route::get('/portal-aspirante', ApplicantDashboard::class)->name('applicant.portal');
        Route::get('/portal-aspirante/solicitud', AdmissionsRegister::class)->name('applicant.admission-form');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/admissions/document/{admission}/{key}', [AdmissionDocumentController::class, 'show'])->name('admissions.document');
});

// --- RUTAS PARA CAMBIO DE CONTRASEÑA OBLIGATORIO ---
Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])
        ->name('password.force_change');
    Route::post('/force-password-change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])
        ->name('password.force_update');
});

require __DIR__.'/auth.php';