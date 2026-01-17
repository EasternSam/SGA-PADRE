<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Student; // Asegúrate de que estos modelos existan
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
                ['title' => 'Dashboard', 'route' => 'dashboard', 'type' => 'Sistema'],
                ['title' => 'Estudiantes', 'route' => 'admin.students.index', 'type' => 'Módulo'],
                ['Docentes', 'route' => 'admin.teachers.index', 'type' => 'Módulo'],
                ['Académico / Cursos', 'route' => 'admin.courses.index', 'type' => 'Módulo'],
                ['Conceptos de Pago', 'route' => 'admin.finance.concepts', 'type' => 'Finanzas'],
                ['Solicitudes', 'route' => 'admin.requests', 'type' => 'Gestión'],
                ['Reportes', 'route' => 'reports.index', 'type' => 'Reportes'],
            ];

            foreach ($systemPages as $page) {
                // Verificar si la ruta existe para evitar errores
                if (Route::has($page['route'])) {
                    if (stripos($page['title'] ?? $page[0], $this->search) !== false) {
                        $results->push([
                            'title' => $page['title'] ?? $page[0],
                            'subtitle' => 'Ir a sección',
                            'type' => $page['type'],
                            'url' => route($page['route']),
                            'icon' => 'fas fa-columns'
                        ]);
                    }
                }
            }

            // 2. Buscar Estudiantes (Si existe el modelo)
            if (class_exists(\App\Models\Student::class)) {
                // Intenta buscar por nombre o apellido si existen las columnas, ajusta según tu DB real
                try {
                    $students = \App\Models\Student::query()
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->take(5)
                        ->get();

                    foreach ($students as $student) {
                        $results->push([
                            'title' => $student->name . ' ' . ($student->last_name ?? ''),
                            'subtitle' => $student->email ?? 'Estudiante registrado',
                            'type' => 'Estudiante',
                            // Ajusta esta ruta a la de perfil de estudiante real si tienes una
                            'url' => Route::has('admin.students.profile') ? route('admin.students.profile', $student->id) : '#', 
                            'icon' => 'fas fa-user-graduate'
                        ]);
                    }
                } catch (\Exception $e) {
                    // Ignorar errores si columnas no existen
                }
            }

            // 3. Buscar Usuarios (Admins/Profesores)
            $users = User::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->take(3)
                ->get();

            foreach ($users as $user) {
                $results->push([
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'type' => 'Usuario',
                    'url' => '#', // Puedes enlazar al perfil si deseas
                    'icon' => 'fas fa-user'
                ]);
            }
            
            // 4. Buscar Cursos (Si existe el modelo)
            if (class_exists(\App\Models\Course::class)) {
                try {
                    $courses = \App\Models\Course::where('name', 'like', '%' . $this->search . '%')
                        ->take(3)
                        ->get();

                    foreach ($courses as $course) {
                        $results->push([
                            'title' => $course->name,
                            'subtitle' => 'Curso Académico',
                            'type' => 'Curso',
                            'url' => '#', // Ajustar ruta
                            'icon' => 'fas fa-book'
                        ]);
                    }
                } catch (\Exception $e) {}
            }
        }

        return view('livewire.global-search', [
            'results' => $results
        ]);
    }
}