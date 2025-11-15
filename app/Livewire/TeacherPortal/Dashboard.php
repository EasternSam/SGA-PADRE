<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\CourseSchedule; // Importar el modelo
use Illuminate\Support\Facades\Auth; // Para obtener el usuario autenticado
use Illuminate\Database\Eloquent\Collection; // Para inicializar la colección
use Illuminate\Support\Str; // <-- MEJORA: Para limitar texto

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    // Propiedades públicas para la vista
    public Collection $courseSchedules;
    public $teacher;
    public $totalSchedules = 0;
    public $totalStudents = 0;

    // --- MEJORAS ---
    public $nextClassToday = null; // Para la tarjeta de "Próxima Clase"
    public $chartLabels; // Para el gráfico
    public $chartData; // Para el gráfico

    /**
     * El método mount() se ejecuta una vez cuando el componente se carga.
     * Aquí es donde cargaremos los datos.
     */
    public function mount()
    {
        // Obtener el profesor autenticado
        $this->teacher = Auth::user();
        $teacherId = $this->teacher->id;

        // Cargar los horarios de los cursos para este profesor
        $this->courseSchedules = CourseSchedule::where('teacher_id', $teacherId)
            ->with([
                'module.course', // Carga el módulo y el curso asociado
            ])
            ->withCount('enrollments') // Esto crea la propiedad 'enrollments_count'
            ->get();

        // --- MEJORAS: Calcular KPIs ---
        $this->totalSchedules = $this->courseSchedules->count();
        $this->totalStudents = $this->courseSchedules->sum('enrollments_count');

        // --- MEJORA: Lógica para "Próxima Clase" ---
        $todayName = ucfirst(now()->isoFormat('dddd')); // e.g., 'Sábado'
        $nowTime = now()->format('H:i:s');

        $this->nextClassToday = CourseSchedule::where('teacher_id', $teacherId)
            ->whereJsonContains('days_of_week', $todayName)
            ->where('end_time', '>', $nowTime) // Clase que aún no ha terminado
            ->orderBy('start_time', 'asc')
            ->with('module.course')
            ->first();

        // --- MEJORA: Preparar datos del Chart ---
        // Usamos el nombre del curso y módulo como etiqueta
        $this->chartLabels = $this->courseSchedules->map(function ($schedule) {
            $courseName = $schedule->module->course->name ?? 'Curso desc.';
            $moduleName = $schedule->module->name ?? 'Módulo desc.';
            // Acortar para que quepa en el gráfico
            return Str::limit($courseName . ' (' . $moduleName . ')', 30);
        });
        $this->chartData = $this->courseSchedules->pluck('enrollments_count');
    }

    /**
     * Ahora el método render solo necesita mostrar la vista.
     * La vista tendrá acceso automáticamente a las propiedades públicas.
     */
    public function render()
    {
        return view('livewire.teacher-portal.dashboard');
    }
}