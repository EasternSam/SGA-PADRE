<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MoodleApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            // 1. INTENTO A: Plugin oficial User Key (El método elegante)
            $loginUrl = $moodleService->getLoginUrl($user->email);

            if ($loginUrl) {
                return redirect()->away($loginUrl);
            }

            // 2. INTENTO B: Auto-Submit Form (El método robusto si falla el plugin)
            // Si llegamos aquí, es porque el plugin userkey no está instalado/habilitado.
            
            // a) Buscar el usuario en Moodle para obtener su ID y Username real
            $moodleUser = $moodleService->getMoodleUserByEmail($user->email);
            
            if (!$moodleUser) {
                // Si no existe, intentar crearlo sobre la marcha con contraseña default
                $tempPassword = 'Temp' . Str::random(10) . '!';
                $moodleId = $moodleService->syncUser($user, $tempPassword);
                
                if ($moodleId) {
                    $moodleUser = ['id' => $moodleId, 'username' => strtolower(explode('@', $user->email)[0])]; // Aproximación
                    // Volvemos a buscar para asegurar los datos correctos
                    $moodleUser = $moodleService->getMoodleUserByEmail($user->email);
                } else {
                    return back()->with('error', 'El usuario no existe en el Aula Virtual y no pudo ser creado.');
                }
            }

            // b) Generar una contraseña temporal compleja
            // Moodle 5.0 requiere: 8 chars, 1 mayúscula, 1 minúscula, 1 número, 1 símbolo
            $newPassword = 'Sga' . Str::random(8) . rand(10,99) . '!';

            // c) Actualizar la contraseña en Moodle vía API
            $updated = $moodleService->updateUserPassword($moodleUser['id'], $newPassword);

            if ($updated) {
                // d) Retornar una vista que hace POST automático al login de Moodle
                $moodleUrl = config('services.moodle.url'); // Asegúrate que termine sin slash o ajústalo
                $loginAction = rtrim($moodleUrl, '/') . '/login/index.php';

                return view('auth.moodle-autosubmit', [
                    'action' => $loginAction,
                    'username' => $moodleUser['username'],
                    'password' => $newPassword
                ]);
            }

            return back()->with('error', 'No se pudo sincronizar las credenciales con el Aula Virtual.');

        } catch (\Exception $e) {
            Log::error("Moodle SSO Error para usuario {$user->id}: " . $e->getMessage());
            return back()->with('error', 'Error de conexión con el Aula Virtual.');
        }
    }
}