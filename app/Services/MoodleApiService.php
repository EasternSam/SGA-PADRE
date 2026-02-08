<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class MoodleApiService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        // Asumimos que estas variables estarán en tu .env
        // MOODLE_URL=https://tu-moodle.com
        // MOODLE_TOKEN=tu_token_generado_en_moodle
        $this->baseUrl = config('services.moodle.url');
        $this->token = config('services.moodle.token');
    }

    /**
     * Enviar petición genérica a Moodle
     */
    protected function makeRequest($function, $params = [])
    {
        $params['wstoken'] = $this->token;
        $params['wsfunction'] = $function;
        $params['moodlewsrestformat'] = 'json';

        try {
            $response = Http::asForm()->post($this->baseUrl . '/webservice/rest/server.php', $params);
            
            if ($response->failed()) {
                Log::error("Moodle API Error ({$function}): " . $response->body());
                return null;
            }

            $data = $response->json();

            if (isset($data['exception'])) {
                Log::error("Moodle API Exception ({$function}): " . json_encode($data));
                return null;
            }

            return $data;

        } catch (\Exception $e) {
            Log::error("Moodle Connection Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener listado de cursos de Moodle
     */
    public function getCourses()
    {
        // core_course_get_courses devuelve la lista de cursos disponibles
        $courses = $this->makeRequest('core_course_get_courses');

        // Moodle a veces devuelve un array directo, verifica si es válido
        if (is_array($courses)) {
            return $courses;
        }

        return [];
    }

    /**
     * Busca un usuario por email, si no existe, lo crea.
     */
    public function syncUser(User $user, $password)
    {
        // 1. Buscar usuario existente
        $users = $this->makeRequest('core_user_get_users_by_field', [
            'field' => 'email',
            'values' => [$user->email]
        ]);

        if (!empty($users)) {
            return $users[0]['id'];
        }

        // 2. Crear usuario si no existe
        $newUser = $this->makeRequest('core_user_create_users', [
            'users' => [
                [
                    'username' => strtolower(explode('@', $user->email)[0] . rand(100,999)), // Username único básico
                    'password' => $password,
                    'firstname' => $user->name,
                    'lastname' => $user->last_name ?? 'Estudiante', // Asegúrate de tener apellido o usa un default
                    'email' => $user->email,
                    'auth' => 'manual',
                ]
            ]
        ]);

        if ($newUser && isset($newUser[0]['id'])) {
            return $newUser[0]['id'];
        }

        return null;
    }

    /**
     * Actualiza la contraseña de un usuario en Moodle (Plan B para SSO)
     */
    public function updateUserPassword($moodleUserId, $newPassword)
    {
        return $this->makeRequest('core_user_update_users', [
            'users' => [
                [
                    'id' => $moodleUserId,
                    'password' => $newPassword
                ]
            ]
        ]);
    }

    /**
     * Matricular usuario en un curso
     */
    public function enrollUser($moodleUserId, $moodleCourseId)
    {
        return $this->makeRequest('enrol_manual_enrol_users', [
            'enrolments' => [
                [
                    'roleid' => 5, // 5 suele ser el rol de estudiante
                    'userid' => $moodleUserId,
                    'courseid' => $moodleCourseId
                ]
            ]
        ]);
    }

    /**
     * Generar URL de acceso directo (SSO)
     * Requiere plugin 'auth_userkey'. Si falla, retorna null.
     */
    public function getLoginUrl($userEmail)
    {
        $users = $this->makeRequest('core_user_get_users_by_field', [
            'field' => 'email',
            'values' => [$userEmail]
        ]);

        if (empty($users)) return null;
        
        $moodleUser = $users[0];

        $keyData = $this->makeRequest('auth_userkey_request_login_url', [
            'user' => [
                'id' => $moodleUser['id']
            ]
        ]);

        if ($keyData && isset($keyData['loginurl'])) {
            return $keyData['loginurl'];
        }
        
        return null; // Retornamos null para activar el Plan B en el controlador
    }
    
    public function getMoodleUserByEmail($email)
    {
        $users = $this->makeRequest('core_user_get_users_by_field', [
            'field' => 'email',
            'values' => [$email]
        ]);

        return !empty($users) ? $users[0] : null;
    }
}