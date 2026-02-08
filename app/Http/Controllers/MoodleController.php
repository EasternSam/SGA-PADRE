<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MoodleApiService;
use Illuminate\Support\Facades\Log;

class MoodleController extends Controller
{
    /**
     * Gestiona el Single Sign-On (SSO) hacia Moodle.
     */
    public function sso(MoodleApiService $moodleService)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            // Intentar obtener la URL de autologin (tokenizada)
            // Esto requiere que el plugin 'auth_userkey' esté configurado en Moodle
            $loginUrl = $moodleService->getLoginUrl($user->email);

            if ($loginUrl) {
                return redirect()->away($loginUrl);
            }

            // Fallback: Si no se puede generar el token, redirigir al login normal
            // Asumimos que la URL base está en config o en el servicio
            $baseUrl = config('services.moodle.url');
            
            if ($baseUrl) {
                return redirect()->away($baseUrl . '/login/index.php');
            }

            return back()->with('error', 'La URL de Moodle no está configurada en el sistema.');

        } catch (\Exception $e) {
            Log::error("Moodle SSO Error para usuario {$user->id}: " . $e->getMessage());
            return back()->with('error', 'No se pudo conectar con el Aula Virtual en este momento.');
        }
    }
}