<?php

namespace App\Livewire\Dashboard;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Collection;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    // Estadísticas (Contadores Estáticos)
    public $totalStudents = 0;
    public $totalCourses = 0;
    public $totalTeachers = 0;
    public $totalEnrollments = 0;
    
    // Estado del Filtro de Inscripciones
    public $enrollmentFilter = 'all'; // Valores: 'all', 'Pendiente', 'Activo'

    // Colección para actividades (no requiere filtro por ahora)
    public Collection $recentActivities;

    /**
     * Carga inicial de datos estáticos.
     */
    public function mount()
    {
        // Inicializar colecciones para evitar errores de tipo
        $this->recentActivities = new Collection();

        try {
            // 1. Cargar Contadores (Solo una vez al inicio)
            $this->totalStudents = Student::count();
            $this->totalCourses = Course::count();
            $this->totalEnrollments = Enrollment::count();
            $this->totalTeachers = User::role('Profesor')->count();

            // 2. Cargar Actividad Reciente (Timeline)
            // Cargamos esto en mount porque no cambia con la interacción del usuario en esta vista
            $this->recentActivities = ActivityLog::with('causer')
                ->latest()
                ->take(6)
                ->get();

        } catch (\Exception $e) {
            \Log::error("Error cargando Dashboard Stats: " . $e->getMessage());
        }
    }

    /**
     * Método para cambiar el filtro desde la vista.
     */
    public function setFilter($status)
    {
        $this->enrollmentFilter = $status;
    }

    /**
     * Renderiza la vista y carga datos dinámicos.
     */
    public function render()
    {
        // Construcción de la consulta de inscripciones
        $query = Enrollment::with([
            'student', 
            'courseSchedule.module.course', 
            'courseSchedule.teacher'
        ])->latest();

        // Aplicar filtro si no es 'all'
        if ($this->enrollmentFilter !== 'all') {
            $query->where('status', $this->enrollmentFilter);
        }

        // Obtener resultados limitados (ej. 5 últimos del filtro seleccionado)
        $recentEnrollments = $query->take(5)->get();

        return view('Livewire.Dashboard.index', [
            'recentEnrollments' => $recentEnrollments,
        ]);
    }
}