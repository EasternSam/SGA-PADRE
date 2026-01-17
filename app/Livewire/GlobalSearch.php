<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB; 

class GlobalSearch extends Component
{
    public $search = '';
    public $isOpen = false;

    public function updatedSearch()
    {
        $this->isOpen = !empty($this->search);
    }

    public function selectResult()
    {
        $this->isOpen = false;
        $this->search = '';
    }

    public function render()
    {
        $results = collect();
        
        // Limpiamos espacios extra al inicio/final
        $term = trim($this->search);

        if (strlen($term) >= 2) {
            // Término flexible para encontrar "Samuel Diaz" aunque haya "Samuel Antonio Diaz"
            $flexibleTerm = '%' . str_replace(' ', '%', $term) . '%';

            // 1. Buscar en Secciones del Sistema
            $systemPages = [
                ['title' => 'Dashboard', 'route' => 'dashboard', 'type' => 'Sistema', 'icon' => 'fas fa-home'],
                ['title' => 'Lista de Estudiantes', 'route' => 'admin.students.index', 'type' => 'Módulo', 'icon' => 'fas fa-user-graduate'],
                ['title' => 'Lista de Docentes', 'route' => 'admin.teachers.index', 'type' => 'Módulo', 'icon' => 'fas fa-chalkboard-teacher'],
                ['title' => 'Cursos Académicos', 'route' => 'admin.courses.index', 'type' => 'Módulo', 'icon' => 'fas fa-book'],
                ['title' => 'Conceptos de Pago', 'route' => 'admin.finance.concepts', 'type' => 'Finanzas', 'icon' => 'fas fa-file-invoice-dollar'],
                ['title' => 'Solicitudes', 'route' => 'admin.requests', 'type' => 'Gestión', 'icon' => 'fas fa-clipboard-list'],
                ['title' => 'Reportes', 'route' => 'reports.index', 'type' => 'Reportes', 'icon' => 'fas fa-chart-line'],
            ];

            foreach ($systemPages as $page) {
                if (Route::has($page['route'])) {
                    if (stripos($page['title'], $term) !== false) {
                        $results->push([
                            'title' => $page['title'],
                            'subtitle' => 'Ir a sección',
                            'type' => $page['type'],
                            'url' => route($page['route']),
                            'icon' => $page['icon']
                        ]);
                    }
                }
            }

            // 2. Buscar Estudiantes (CORREGIDO: Busca a través de la relación 'user')
            if (class_exists(Student::class)) {
                // Buscamos en la tabla 'users' asociada, ya que 'students' no tiene columna 'name'
                $students = Student::query()
                    ->with('user') // Cargamos la relación user
                    ->whereHas('user', function($q) use ($flexibleTerm) {
                        $q->where('name', 'like', $flexibleTerm)
                          ->orWhere('email', 'like', $flexibleTerm);
                    })
                    ->take(5)
                    ->get();

                foreach ($students as $student) {
                    // Obtenemos los datos del usuario asociado
                    $user = $student->user;
                    if (!$user) continue; // Si no hay usuario (data corrupta), saltar

                    $url = '#';
                    if (Route::has('admin.students.profile')) {
                        $url = route('admin.students.profile', $student->id);
                    } elseif (Route::has('admin.students.index')) {
                        $url = route('admin.students.index', ['search' => $user->name]);
                    }

                    // Intentamos mostrar matrícula si existe la columna, sino solo email
                    $subtitle = $user->email;
                    if (isset($student->student_id)) {
                        $subtitle .= ' | Mat: ' . $student->student_id;
                    }

                    $results->push([
                        'title' => $user->name,
                        'subtitle' => $subtitle,
                        'type' => 'Estudiante',
                        'url' => $url,
                        'icon' => 'fas fa-user-graduate'
                    ]);
                }
            }

            // 3. Buscar Usuarios Generales (Admins, Profesores)
            $users = User::with('roles')
                ->where(function($q) use ($flexibleTerm) {
                    $q->where('name', 'like', $flexibleTerm)
                      ->orWhere('email', 'like', $flexibleTerm);
                })
                ->take(5)
                ->get();

            foreach ($users as $user) {
                $roleName = $user->roles->first()?->name ?? 'Usuario';
                
                $displayRole = match(strtolower($roleName)) {
                    'admin', 'super-admin' => 'Administrador',
                    'teacher', 'profesor', 'docente' => 'Profesor',
                    'student', 'estudiante' => 'Estudiante',
                    default => ucfirst($roleName)
                };

                // Evitamos duplicar visualmente si ya salió como estudiante arriba
                if ($displayRole === 'Estudiante') continue;

                $url = '#';
                if ($displayRole === 'Profesor' && Route::has('admin.teachers.index')) {
                    $url = Route::has('admin.teachers.profile') 
                        ? route('admin.teachers.profile', $user->id) // OJO: Chequear si profile usa ID user o ID teacher
                        : route('admin.teachers.index', ['search' => $user->name]);
                } elseif ($displayRole === 'Administrador') {
                    $url = route('profile.edit');
                }

                $results->push([
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'type' => $displayRole,
                    'url' => $url,
                    'icon' => match($displayRole) {
                        'Administrador' => 'fas fa-user-shield',
                        'Profesor' => 'fas fa-chalkboard-teacher',
                        default => 'fas fa-user'
                    }
                ]);
            }
            
            // 4. Buscar Cursos
            if (class_exists(Course::class)) {
                $courses = Course::where('name', 'like', $flexibleTerm)
                    ->take(3)
                    ->get();

                foreach ($courses as $course) {
                    $url = '#';
                    if (Route::has('admin.courses.edit')) {
                        $url = route('admin.courses.edit', $course->id);
                    } elseif (Route::has('admin.courses.index')) {
                        $url = route('admin.courses.index', ['search' => $course->name]);
                    }

                    $results->push([
                        'title' => $course->name,
                        'subtitle' => $course->code ?? 'Curso Académico',
                        'type' => 'Curso',
                        'url' => $url,
                        'icon' => 'fas fa-book-open'
                    ]);
                }
            }
        }

        return view('livewire.global-search', [
            'results' => $results
        ]);
    }
}