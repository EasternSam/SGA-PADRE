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
        $this->baseUrl = config('services.moodle.url');
        $this->token = config('services.moodle.token');
    }

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

    public function getCourses()
    {
        $courses = $this->makeRequest('core_course_get_courses');
        return is_array($courses) ? $courses : [];
    }

    /**
     * Busca o crea un usuario.
     * MODIFICADO: Acepta $customUsername para forzar usar la matrícula.
     */
    public function syncUser(User $user, $password, $customUsername = null)
    {
        // 1. Buscar usuario existente por email
        $users = $this->makeRequest('core_user_get_users_by_field', [
            'field' => 'email',
            'values' => [$user->email]
        ]);

        if (!empty($users)) {
            return $users[0]['id'];
        }

        // Definir el username: Si nos dan uno (matrícula), lo usamos. Si no, usamos el email limpio.
        // Moodle requiere usernames en minúsculas.
        $moodleUsername = $customUsername 
            ? strtolower($customUsername) 
            : strtolower(explode('@', $user->email)[0]);

        // 2. Crear usuario si no existe
        $newUser = $this->makeRequest('core_user_create_users', [
            'users' => [
                [
                    'username' => $moodleUsername, 
                    'password' => $password,
                    'firstname' => $user->name,
                    'lastname' => $user->last_name ?? 'Estudiante',
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

    public function enrollUser($moodleUserId, $moodleCourseId)
    {
        return $this->makeRequest('enrol_manual_enrol_users', [
            'enrolments' => [
                [
                    'roleid' => 5, 
                    'userid' => $moodleUserId,
                    'courseid' => $moodleCourseId
                ]
            ]
        ]);
    }

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
        
        return null; 
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