<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\ActivityLog;
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

        $settings = [
            // Identidad & Contacto
            'institution_name' => $allSettings['institution_name'] ?? 'Instituto Tecnológico de Las Américas',
            'support_email' => $allSettings['support_email'] ?? 'soporte@institucion.edu',
            'contact_phone' => $allSettings['contact_phone'] ?? '',
            'contact_address' => $allSettings['contact_address'] ?? '',
            'website_url' => $allSettings['website_url'] ?? '',
            'social_facebook' => $allSettings['social_facebook'] ?? '',
            'social_instagram' => $allSettings['social_instagram'] ?? '',
            'enable_careers' => ($allSettings['enable_careers'] ?? 'true') === 'true',

            // Marca & Apariencia (Paleta Institucional de 4 Colores)
            'brand_primary_color' => $allSettings['brand_primary_color'] ?? '#bb3902',
            'brand_secondary_color' => $allSettings['brand_secondary_color'] ?? '#1c1917',
            'brand_accent_color' => $allSettings['brand_accent_color'] ?? '#ea580c',
            'brand_neutral_color' => $allSettings['brand_neutral_color'] ?? '#78716c',
            'institution_logo' => $allSettings['institution_logo'] ?? '/branding/logo_1787814439.png',
            'favicon' => $allSettings['favicon'] ?? '/branding/favicon_1777868576.webp',
            'app_icon' => $allSettings['app_icon'] ?? '/branding/app_icon_1777868576.webp',
            'login_banner' => $allSettings['login_banner'] ?? '/branding/login_banner_1787811955.jpg',
            'navbar_type' => $allSettings['navbar_type'] ?? 'solid',
            'navbar_gradient_start' => $allSettings['navbar_gradient_start'] ?? '#bb3902',
            'navbar_gradient_end' => $allSettings['navbar_gradient_end'] ?? '#1a0a53',
            'navbar_gradient_direction' => $allSettings['navbar_gradient_direction'] ?? 'to bottom right',

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

            // Pasarelas & DGII
            'cardnet_merchant_id' => $allSettings['cardnet_merchant_id'] ?? env('CARDNET_MERCHANT_ID', ''),
            'cardnet_terminal_id' => $allSettings['cardnet_terminal_id'] ?? env('CARDNET_TERMINAL_ID', ''),
            'enable_electronic_billing' => ($allSettings['enable_electronic_billing'] ?? 'false') === 'true' || ($allSettings['enable_electronic_billing'] ?? '') === '1',
            'ecf_rnc_emisor' => $allSettings['ecf_rnc_emisor'] ?? '101000000',

            // Servidor SMTP
            'smtp_host' => $allSettings['smtp_host'] ?? env('MAIL_HOST', ''),
            'smtp_port' => $allSettings['smtp_port'] ?? env('MAIL_PORT', '587'),
            'smtp_username' => $allSettings['smtp_username'] ?? env('MAIL_USERNAME', ''),
            'smtp_password' => $allSettings['smtp_password'] ?? '',
            'smtp_encryption' => $allSettings['smtp_encryption'] ?? env('MAIL_ENCRYPTION', 'tls'),
            'smtp_from_address' => $allSettings['smtp_from_address'] ?? env('MAIL_FROM_ADDRESS', 'no-reply@institucion.edu'),
        ];

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
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

            // Finance / Cardnet / DGII
            'cardnet_merchant_id' => 'nullable|string|max:50',
            'cardnet_terminal_id' => 'nullable|string|max:50',
            'enable_electronic_billing' => 'nullable|boolean',
            'ecf_rnc_emisor' => 'nullable|string|max:20',

            // SMTP
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|string|max:10',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|max:20',
            'smtp_from_address' => 'nullable|string|max:255',
        ]);

        if (isset($validated['brand_primary_color'])) {
            if (empty($validated['navbar_gradient_start']) || $validated['navbar_gradient_start'] === '#0b2c79') {
                $validated['navbar_gradient_start'] = $validated['brand_primary_color'];
            }
        }

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
                        'brand_primary_color' => $validated['brand_primary_color'] ?? '#0b2c79',
                        'navbar_type' => $validated['navbar_type'] ?? 'solid',
                        'navbar_gradient_start' => $validated['navbar_gradient_start'] ?? '#0b2c79',
                        'navbar_gradient_end' => $validated['navbar_gradient_end'] ?? '#1a0a53',
                        'navbar_gradient_direction' => $validated['navbar_gradient_direction'] ?? 'to bottom right',
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
}
