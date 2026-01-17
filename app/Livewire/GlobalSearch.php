<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB; // Importante para la concatenación SQL

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

            // 2. Buscar Estudiantes (Con CONCATENACIÓN)
            if (class_exists(Student::class)) {
                $students = Student::query()
                    ->where(function($q) use ($term) {
                        $likeTerm = '%' . $term . '%';
                        $q->where('name', 'like', $likeTerm)
                          ->orWhere('last_name', 'like', $likeTerm)
                          ->orWhere('email', 'like', $likeTerm)
                          // --- LA MEJORA CLAVE ---
                          // Busca en "Nombre Apellido" combinado
                          ->orWhereRaw("CONCAT(name, ' ', COALESCE(last_name, '')) LIKE ?", [$likeTerm]);
                    })
                    ->take(5)
                    ->get();

                foreach ($students as $student) {
                    $url = '#';
                    if (Route::has('admin.students.profile')) {
                        $url = route('admin.students.profile', $student->id);
                    } elseif (Route::has('admin.students.index')) {
                        $url = route('admin.students.index', ['search' => $student->name]);
                    }

                    $results->push([
                        'title' => $student->name . ' ' . ($student->last_name ?? ''),
                        'subtitle' => $student->email ?? 'Matrícula: ' . ($student->student_id ?? 'N/A'),
                        'type' => 'Estudiante',
                        'url' => $url,
                        'icon' => 'fas fa-user-graduate'
                    ]);
                }
            }

            // 3. Buscar Usuarios
            $users = User::with('roles')
                ->where(function($q) use ($term) {
                    $likeTerm = '%' . $term . '%';
                    $q->where('name', 'like', $likeTerm)
                      ->orWhere('email', 'like', $likeTerm);
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

                $url = '#';
                
                if ($displayRole === 'Estudiante' && class_exists(Student::class)) {
                    $st = Student::where('user_id', $user->id)->first();
                    if ($st && Route::has('admin.students.profile')) {
                        $url = route('admin.students.profile', $st->id);
                    }
                } elseif ($displayRole === 'Profesor' && Route::has('admin.teachers.index')) {
                    $url = Route::has('admin.teachers.profile') 
                        ? route('admin.teachers.profile', $user->id)
                        : route('admin.teachers.index', ['search' => $user->name]);
                } elseif ($displayRole === 'Administrador') {
                    $url = route('profile.edit');
                }

                // Evitamos duplicar si ya salió como estudiante arriba
                if ($displayRole !== 'Estudiante') { 
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
            }
            
            // 4. Buscar Cursos
            if (class_exists(Course::class)) {
                $courses = Course::where('name', 'like', '%' . $term . '%')
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