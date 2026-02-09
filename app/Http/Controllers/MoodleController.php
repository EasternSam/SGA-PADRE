<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MoodleApiService;
use Illuminate\Support\Facades\Log;

class MoodleController extends Controller
{
    public function sso(MoodleApiService $moodleService)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            // Intentar obtener la URL de autologin (Plugin auth_userkey)
            $loginUrl = $moodleService->getLoginUrl($user->email);

            if ($loginUrl) {
                return redirect()->away($loginUrl);
            }

            // Fallback: Si el plugin no está activo, redirigimos al login manual.
            // NO intentamos cambiar la contraseña.
            $baseUrl = config('services.moodle.url');
            
            if ($baseUrl) {
                return redirect()->away(rtrim($baseUrl, '/') . '/login/index.php');
            }

            return back()->with('error', 'La URL de Moodle no está configurada.');

        } catch (\Exception $e) {
            Log::error("Moodle SSO Error: " . $e->getMessage());
            return back()->with('error', 'No se pudo conectar con el Aula Virtual.');
        }
    }
}