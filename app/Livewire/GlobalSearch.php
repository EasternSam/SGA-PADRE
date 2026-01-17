<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Facades\Route;

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

        if (strlen($this->search) >= 2) {
            // 1. Buscar en Secciones del Sistema (Navegación Rápida)
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
                    if (stripos($page['title'], $this->search) !== false) {
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

            // 2. Buscar Estudiantes (Prioridad Alta)
            if (class_exists(Student::class)) {
                $students = Student::query()
                    ->where(function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->take(5)
                    ->get();

                foreach ($students as $student) {
                    // Intentar generar ruta al perfil, si no existe, ir al índice filtrado o fallback
                    $url = '#';
                    if (Route::has('admin.students.profile')) {
                        $url = route('admin.students.profile', $student->id);
                    } elseif (Route::has('admin.students.index')) {
                        $url = route('admin.students.index', ['search' => $student->name]);
                    }

                    $results->push([
                        'title' => $student->name . ' ' . ($student->last_name ?? ''),
                        'subtitle' => $student->email ?? 'Matrícula: ' . ($student->student_id ?? 'N/A'),
                        'type' => 'Estudiante', // Rol explícito
                        'url' => $url,
                        'icon' => 'fas fa-user-graduate'
                    ]);
                }
            }

            // 3. Buscar Usuarios (Admins, Profesores, Staff)
            // Filtramos para no duplicar estudiantes si ya los buscamos arriba, o simplemente mostramos roles clave
            $users = User::with('roles')
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->take(5)
                ->get();

            foreach ($users as $user) {
                // Obtener el rol real en español
                $roleName = $user->roles->first()?->name ?? 'Usuario';
                
                // Traducción de roles
                $displayRole = match(strtolower($roleName)) {
                    'admin', 'super-admin' => 'Administrador',
                    'teacher', 'profesor', 'docente' => 'Profesor',
                    'student', 'estudiante' => 'Estudiante', // Por si sale aquí también
                    default => ucfirst($roleName)
                };

                // Si es estudiante y ya lo mostramos arriba, podríamos saltarlo, 
                // pero por seguridad definimos URL basada en rol
                $url = '#';
                
                if ($displayRole === 'Estudiante' && class_exists(Student::class)) {
                    // Intentar buscar su registro de estudiante asociado
                    $st = Student::where('user_id', $user->id)->first();
                    if ($st && Route::has('admin.students.profile')) {
                        $url = route('admin.students.profile', $st->id);
                    }
                } elseif ($displayRole === 'Profesor' && Route::has('admin.teachers.index')) {
                    // Si hay perfil de profesor, úsalo, sino al index
                    $url = Route::has('admin.teachers.profile') 
                        ? route('admin.teachers.profile', $user->id) // Asumiendo ID de usuario o buscar ID profe
                        : route('admin.teachers.index', ['search' => $user->name]);
                } elseif ($displayRole === 'Administrador') {
                    $url = route('profile.edit'); // O admin.users.index si existe
                }

                // Evitar duplicados visuales si es estudiante y ya salió en la query de Student
                if ($displayRole !== 'Estudiante') { 
                    $results->push([
                        'title' => $user->name,
                        'subtitle' => $user->email,
                        'type' => $displayRole, // Aquí dice "Profesor", "Admin", etc.
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
                $courses = Course::where('name', 'like', '%' . $this->search . '%')
                    ->take(3)
                    ->get();

                foreach ($courses as $course) {
                    // Ruta a editar curso o índice
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