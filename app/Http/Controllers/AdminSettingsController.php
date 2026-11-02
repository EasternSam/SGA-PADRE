<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\NcfSequence;
use App\Models\ActivityLog;
use App\Support\BrandingDefaults;
use App\Services\WordpressApiService;
use App\Services\MoodleApiService;
use App\Services\BillsApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettingsController extends Controller
{
    /**
     * Display configuration hub.
     */
    public function index(): Response
    {
        $allSettings = Setting::all()->pluck('value', 'key')->toArray();

        // Check active pairing code
        $pairingCode = null;
        $pairingExpiresAt = null;
        $pairingDataJson = $allSettings['wp_pairing_data'] ?? null;
        if (!empty($pairingDataJson)) {
            $pairingData = json_decode($pairingDataJson, true);
            if (is_array($pairingData) && isset($pairingData['code']) && now()->timestamp <= ($pairingData['expires_at'] ?? 0)) {
                $pairingCode = $pairingData['code'];
                $pairingExpiresAt = $pairingData['expires_at'];
            }
        }

        $wpApiUrl = $allSettings['wp_api_url'] ?? config('services.wordpress.base_uri', env('WP_API_BASE_URI', ''));
        $wpApiSecret = $allSettings['wp_api_secret'] ?? config('services.wordpress.secret', env('WP_API_SECRET', ''));
        $moodleUrl = $allSettings['moodle_url'] ?? config('services.moodle.url', env('MOODLE_URL', ''));
        $moodleToken = $allSettings['moodle_token'] ?? config('services.moodle.token', env('MOODLE_TOKEN', ''));

        $branding = BrandingDefaults::resolve($allSettings);

        $settings = [
            // Identidad & Contacto
            'institution_name' => $branding['name'],
            'support_email' => !empty($allSettings['support_email']) ? $allSettings['support_email'] : 'soporte@institucion.edu',
            'contact_phone' => $allSettings['contact_phone'] ?? '',
            'contact_address' => $allSettings['contact_address'] ?? '',
            'website_url' => $allSettings['website_url'] ?? '',
            'social_facebook' => $allSettings['social_facebook'] ?? '',
            'social_instagram' => $allSettings['social_instagram'] ?? '',
            'enable_careers' => ($allSettings['enable_careers'] ?? 'true') === 'true',

            // Marca & Apariencia (Paleta Institucional de 4 Colores)
            'brand_primary_color' => $branding['primary_color'],
            'brand_secondary_color' => $branding['secondary_color'],
            'brand_accent_color' => $branding['accent_color'],
            'brand_neutral_color' => $branding['neutral_color'],
            'institution_logo' => $branding['logo'],
            'sidebar_logo_height' => $branding['sidebar_logo_height'],
            'favicon' => $branding['favicon'],
            'app_icon' => $branding['app_icon'],
            'login_banner' => $branding['login_banner'],
            'navbar_type' => $branding['navbar_type'],
            'navbar_gradient_start' => $branding['navbar_gradient_start'],
            'navbar_gradient_end' => $branding['navbar_gradient_end'],
            'navbar_gradient_direction' => $branding['navbar_gradient_direction'],

            // WordPress / Academic+ Catalog
            'wp_api_url' => $wpApiUrl,
            'wp_api_secret' => $wpApiSecret,
            'wp_connected' => !empty($wpApiUrl) && !empty($wpApiSecret),

            // Moodle (Aula Virtual)
            'moodle_url' => $moodleUrl,
            'moodle_token' => $moodleToken,
            'moodle_connected' => !empty($moodleUrl) && !empty($moodleToken),

            // Facturación Externa (Bills)
            'enable_bills_invoicing' => ($allSettings['enable_bills_invoicing'] ?? 'false') === 'true' || ($allSettings['enable_bills_invoicing'] ?? '') === '1',
            'bills_api_url' => $allSettings['bills_api_url'] ?? 'https://btest.gridbase.com.do/api/v1',
            'bills_api_token' => $allSettings['bills_api_token'] ?? '',
            'bills_default_tax_rate' => $allSettings['bills_default_tax_rate'] ?? '18',

            // Pasarelas & DGII (Gridbase Bills Core)
            'cardnet_merchant_id' => $allSettings['cardnet_merchant_id'] ?? env('CARDNET_MERCHANT_ID', ''),
            'cardnet_terminal_id' => $allSettings['cardnet_terminal_id'] ?? env('CARDNET_TERMINAL_ID', ''),
            'enable_electronic_billing' => ($allSettings['enable_electronic_billing'] ?? 'true') === 'true' || ($allSettings['enable_electronic_billing'] ?? '') === '1',
            'ecf_rnc_emisor' => $allSettings['ecf_rnc_emisor'] ?? '101142456',
            'ecf_razon_social' => $allSettings['ecf_razon_social'] ?? ($branding['name'] ?? ''),
            'ecf_nombre_comercial' => $allSettings['ecf_nombre_comercial'] ?? ($branding['name'] ?? ''),
            'ecf_municipio' => $allSettings['ecf_municipio'] ?? '010100',
            'ecf_provincia' => $allSettings['ecf_provincia'] ?? '010000',
            'ecf_environment' => $allSettings['ecf_environment'] ?? 'testing',
            'ecf_certificate_path' => $allSettings['ecf_certificate_path'] ?? '',
            'ecf_certificate_password' => $allSettings['ecf_certificate_password'] ?? '',
            'ecf_ncf_expiry_date' => $allSettings['ecf_ncf_expiry_date'] ?? '2028-12-31',

            // Servidor SMTP
            'smtp_host' => $allSettings['smtp_host'] ?? env('MAIL_HOST', ''),
            'smtp_port' => $allSettings['smtp_port'] ?? env('MAIL_PORT', '587'),
            'smtp_username' => $allSettings['smtp_username'] ?? env('MAIL_USERNAME', ''),
            'smtp_password' => $allSettings['smtp_password'] ?? '',
            'smtp_encryption' => $allSettings['smtp_encryption'] ?? env('MAIL_ENCRYPTION', 'tls'),
            'smtp_from_address' => $allSettings['smtp_from_address'] ?? env('MAIL_FROM_ADDRESS', 'no-reply@institucion.edu'),
        ];

        NcfSequence::ensureDefaults();
        $ncfSequences = NcfSequence::orderBy('type_code')->get()->map(function ($seq) {
            $padLen = strtoupper((string) $seq->series) === 'B' ? 8 : 10;
            $nextNum = $seq->current_sequence + 1;
            return [
                'id' => $seq->id,
                'name' => $seq->name,
                'type_code' => $seq->type_code,
                'series' => $seq->series,
                'current_sequence' => (int) $seq->current_sequence,
                'limit_sequence' => (int) $seq->limit_sequence,
                'expiration_date' => $seq->expiration_date ? $seq->expiration_date->format('Y-m-d') : '2028-12-31',
                'is_active' => (bool) $seq->is_active,
                'preview_next' => strtoupper((string) $seq->series) . $seq->type_code . str_pad((string) $nextNum, $padLen, '0', STR_PAD_LEFT),
            ];
        });

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'ncfSequences' => $ncfSequences,
            'pairingCode' => $pairingCode,
            'pairingCodeExpiresAt' => $pairingExpiresAt,
            'apiBaseUrl' => url('/api'),
        ]);
    }

    /**
     * Save settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:255',
            'website_url' => 'nullable|string|max:255',
            'social_facebook' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            'enable_careers' => 'nullable|boolean',

            'brand_primary_color' => 'required|string|max:255',
            'brand_secondary_color' => 'nullable|string|max:255',
            'brand_accent_color' => 'nullable|string|max:255',
            'brand_neutral_color' => 'nullable|string|max:255',
            'institution_logo' => 'nullable|string|max:500',
            'sidebar_logo_height' => 'nullable|integer|min:24|max:120',
            'favicon' => 'nullable|string|max:500',
            'app_icon' => 'nullable|string|max:500',
            'login_banner' => 'nullable|string|max:500',
            'navbar_type' => 'nullable|string|in:solid,gradient',
            'navbar_gradient_start' => 'nullable|string|max:20',
            'navbar_gradient_end' => 'nullable|string|max:20',
            'navbar_gradient_direction' => 'nullable|string|max:50',

            // WordPress / Academic+ Catalog
            'wp_api_url' => 'nullable|string|max:255',
            'wp_api_secret' => 'nullable|string|max:255',

            // Moodle
            'moodle_url' => 'nullable|string|max:255',
            'moodle_token' => 'nullable|string|max:255',

            // Bills
            'enable_bills_invoicing' => 'nullable|boolean',
            'bills_api_url' => 'nullable|string|max:255',
            'bills_api_token' => 'nullable|string|max:255',
            'bills_default_tax_rate' => 'nullable|string|max:10',

            // Finance / Cardnet / DGII (Gridbase Bills Core)
            'cardnet_merchant_id' => 'nullable|string|max:50',
            'cardnet_terminal_id' => 'nullable|string|max:50',
            'enable_electronic_billing' => 'nullable|boolean',
            'ecf_rnc_emisor' => 'nullable|string|max:20',
            'ecf_razon_social' => 'nullable|string|max:255',
            'ecf_nombre_comercial' => 'nullable|string|max:255',
            'ecf_municipio' => 'nullable|string|max:10',
            'ecf_provincia' => 'nullable|string|max:10',
            'ecf_environment' => 'nullable|string|in:testing,certification,production',
            'ecf_certificate_path' => 'nullable|string|max:255',
            'ecf_certificate_password' => 'nullable|string|max:255',
            'ecf_ncf_expiry_date' => 'nullable|string|max:20',
            'ncf_sequences' => 'nullable|array',

            // SMTP
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|string|max:10',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|max:20',
            'smtp_from_address' => 'nullable|string|max:255',
        ]);

        if (isset($validated['brand_primary_color'])) {
            if (empty($validated['navbar_gradient_start']) || $validated['navbar_gradient_start'] === BrandingDefaults::PRIMARY_COLOR) {
                $validated['navbar_gradient_start'] = $validated['brand_primary_color'];
            }
        }

        $sequencesPayload = $validated['ncf_sequences'] ?? null;
        unset($validated['ncf_sequences']);

        foreach ($validated as $key => $value) {
            $group = 'general';
            $type = 'string';

            if (str_starts_with($key, 'wp_') || str_starts_with($key, 'moodle_') || str_starts_with($key, 'bills_') || $key === 'enable_bills_invoicing') {
                $group = 'apis';
            } elseif (str_starts_with($key, 'cardnet_') || str_starts_with($key, 'ecf_') || $key === 'enable_electronic_billing') {
                $group = 'finance';
            } elseif (str_starts_with($key, 'smtp_')) {
                $group = 'smtp';
            }

            if (is_bool($value)) {
                $valStr = $value ? 'true' : 'false';
            } else {
                $valStr = $value ?? '';
            }

            Setting::set($key, $valStr, $group, $type);
        }

        if (is_array($sequencesPayload)) {
            foreach ($sequencesPayload as $seqItem) {
                if (!empty($seqItem['id'])) {
                    $seqModel = NcfSequence::find($seqItem['id']);
                    if ($seqModel) {
                        $seqModel->update([
                            'series' => strtoupper(substr(trim((string) ($seqItem['series'] ?? 'E')), 0, 1)) ?: 'E',
                            'current_sequence' => max(0, (int) ($seqItem['current_sequence'] ?? 0)),
                            'limit_sequence' => max(1, (int) ($seqItem['limit_sequence'] ?? 5000)),
                            'expiration_date' => !empty($seqItem['expiration_date']) ? $seqItem['expiration_date'] : '2028-12-31',
                            'is_active' => isset($seqItem['is_active']) ? (bool) $seqItem['is_active'] : true,
                        ]);
                    }
                }
            }
        }

        // Webhook: sync branding to WordPress if configured
        if (!empty($validated['wp_api_url']) && !empty($validated['wp_api_secret'])) {
            try {
                $endpoint = rtrim($validated['wp_api_url'], '/');
                if (str_contains($endpoint, '/wp-json')) {
                    $endpoint = preg_replace('#/wp-json/.*$#', '/wp-json/sga/v1/update-branding/', $endpoint);
                } else {
                    $endpoint .= '/wp-json/sga/v1/update-branding/';
                }

                Http::withHeaders([
                    'X-SGA-Signature' => $validated['wp_api_secret'],
                    'Content-Type' => 'application/json',
                ])->timeout(4)->post($endpoint, [
                    'branding' => [
                        'brand_primary_color' => $validated['brand_primary_color'] ?? BrandingDefaults::PRIMARY_COLOR,
                        'navbar_type' => $validated['navbar_type'] ?? BrandingDefaults::NAVBAR_TYPE,
                        'navbar_gradient_start' => $validated['navbar_gradient_start'] ?? BrandingDefaults::NAVBAR_GRADIENT_START,
                        'navbar_gradient_end' => $validated['navbar_gradient_end'] ?? BrandingDefaults::NAVBAR_GRADIENT_END,
                        'navbar_gradient_direction' => $validated['navbar_gradient_direction'] ?? BrandingDefaults::NAVBAR_GRADIENT_DIRECTION,
                    ]
                ]);
            } catch (\Exception $e) {
                Log::warning('Webhook branding a WP no respondió: ' . $e->getMessage());
            }
        }

        Cache::flush();

        return back()->with('success', 'Configuraciones globales actualizadas exitosamente.');
    }

    /**
     * Generate temporary pairing code for WordPress Academic+ Catalog.
     */
    public function generatePairingCode()
    {
        $code = 'SGA-' . strtoupper(Str::random(6));

        $user = Auth::user();
        if (!$user) {
            $user = \App\Models\User::role('Admin')->first() ?? \App\Models\User::first();
        }

        if (!$user) {
            return back()->with('error', 'No se pudo generar el token: no hay usuarios administradores registrados.');
        }

        $tokenName = 'WordPress Catalog Integration';
        $user->tokens()->where('name', $tokenName)->delete();
        $token = $user->createToken($tokenName)->plainTextToken;

        $wp_api_secret = Str::random(40);
        $expiresAt = now()->addMinutes(15);

        $pairingData = [
            'code' => $code,
            'api_token' => $token,
            'wp_api_secret' => $wp_api_secret,
            'expires_at' => $expiresAt->timestamp,
        ];

        Setting::set('wp_pairing_data', json_encode($pairingData), 'apis', 'json');
        Cache::flush();

        return back()->with('success', "¡Código de enlace generado: {$code}! Úsalo en el plugin de WordPress antes de que expire.");
    }

    /**
     * Sync WordPress students.
     */
    public function syncWordPressStudents(WordpressApiService $wpService)
    {
        @set_time_limit(300);
        try {
            $wpStudents = $wpService->getSgaStudents();
            if (empty($wpStudents)) {
                return back()->with('error', 'No se encontraron estudiantes en WordPress o la conexión falló. Verifica las credenciales.');
            }

            $syncedCount = 0;

            \App\Models\User::withoutEvents(function () use ($wpStudents, &$syncedCount) {
                \App\Models\Student::withoutEvents(function () use ($wpStudents, &$syncedCount) {
                    \App\Models\Enrollment::withoutEvents(function () use ($wpStudents, &$syncedCount) {
                        foreach ($wpStudents as $wpStudent) {
                            $cedula = trim($wpStudent['cedula'] ?? '');
                            $email = trim($wpStudent['email'] ?? '');
                            if (empty($cedula) || empty($email)) continue;

                            $cleanCedula = preg_replace('/[^0-9]/', '', $cedula);

                            $user = \App\Models\User::where('email', $email)->first();
                            if (!$user) {
                                $user = \App\Models\User::create([
                                    'name' => $wpStudent['nombre'] ?? 'Estudiante WP',
                                    'email' => $email,
                                    'password' => \Illuminate\Support\Facades\Hash::make($cleanCedula),
                                    'email_verified_at' => now(),
                                ]);
                                $user->assignRole('Estudiante');
                            }

                            $student = \App\Models\Student::where('cedula', $cedula)
                                ->orWhere('cedula', $cleanCedula)
                                ->orWhere('user_id', $user->id)
                                ->first();

                            $parts = explode(' ', trim($wpStudent['nombre'] ?? ''));
                            $first_name = $parts[0] ?? 'Estudiante';
                            $last_name = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : 'WP';

                            if (!$student) {
                                $student = \App\Models\Student::create([
                                    'user_id' => $user->id,
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'cedula' => $cedula,
                                    'email' => $email,
                                    'mobile_phone' => $wpStudent['telefono'] ?? null,
                                    'status' => 'Activa',
                                ]);
                            }

                            $syncedCount++;
                        }
                    });
                });
            });

            return back()->with('success', "Se han sincronizado exitosamente {$syncedCount} estudiantes desde WordPress.");
        } catch (\Exception $e) {
            Log::error('Error sincronizando estudiantes de WordPress: ' . $e->getMessage());
            return back()->with('error', 'Error al sincronizar estudiantes: ' . $e->getMessage());
        }
    }

    /**
     * Test Moodle API connection.
     */
    public function testMoodle(MoodleApiService $moodleService)
    {
        try {
            $courses = $moodleService->getCourses();
            if ($courses === null) {
                return back()->with('error', 'No se pudo conectar a Moodle. Verifica la URL y el Token en los ajustes.');
            }
            $count = is_array($courses) ? count($courses) : 0;
            return back()->with('success', "¡Conexión exitosa con Moodle! Se detectaron {$count} cursos disponibles en el aula virtual.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error de conexión con Moodle: ' . $e->getMessage());
        }
    }

    /**
     * Probar conexión con Gridbase Bills API.
     */
    public function testBills(BillsApiService $billsService)
    {
        try {
            $result = $billsService->testConnection();
            if (!empty($result['success'])) {
                return back()->with('success', $result['message'] ?? '¡Conexión establecida exitosamente con Gridbase Bills API!');
            }
            return back()->with('error', $result['message'] ?? 'Fallo de conexión con Gridbase Bills. Verifica la URL y el Token en los ajustes.');
        } catch (\Exception $e) {
            Log::error('AdminSettingsController@testBills Error: ' . $e->getMessage());
            return back()->with('error', 'Error de conexión con Gridbase Bills: ' . $e->getMessage());
        }
    }

    /**
     * Subir certificado digital .p12 / .pfx para firma de e-CF (Gridbase Bills Core).
     */
    public function uploadEcfCertificate(Request $request)
    {
        $request->validate([
            'certificate' => 'required|file|max:5120',
            'password' => 'nullable|string|max:255',
        ]);

        $file = $request->file('certificate');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['p12', 'pfx'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo debe tener extensión .p12 o .pfx.',
            ], 422);
        }

        $secureDir = storage_path('app/secure');
        if (!is_dir($secureDir)) {
            @mkdir($secureDir, 0755, true);
        }

        $filename = 'ecf_cert_' . date('Ymd_His') . '.' . $ext;
        $file->move($secureDir, $filename);

        Setting::set('ecf_certificate_path', $filename, 'finance', 'string');
        if ($request->filled('password')) {
            Setting::set('ecf_certificate_password', (string) $request->input('password'), 'finance', 'string');
        }
        Cache::flush();

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'message' => "Certificado digital ({$filename}) almacenado exitosamente en storage/app/secure/.",
        ]);
    }
}
