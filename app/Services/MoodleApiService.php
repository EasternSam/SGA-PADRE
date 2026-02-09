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
     * Retorna array: ['id' => 123, 'was_created' => true/false]
     */
    public function syncUser(User $user, $password, $customUsername = null)
    {
        // 1. Buscar usuario existente por email
        $users = $this->makeRequest('core_user_get_users_by_field', [
            'field' => 'email',
            'values' => [$user->email]
        ]);

        if (!empty($users)) {
            // USUARIO YA EXISTE: Devolvemos ID y flag false
            return [
                'id' => $users[0]['id'],
                'was_created' => false
            ];
        }

        // Definir el username
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
            // USUARIO CREADO: Devolvemos ID y flag true
            return [
                'id' => $newUser[0]['id'],
                'was_created' => true
            ];
        }

        return null;
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
        $keyData = $this->makeRequest('auth_userkey_request_login_url', [
            'user' => [
                'email' => $userEmail 
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