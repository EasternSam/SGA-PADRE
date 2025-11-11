<?php

namespace App\Livewire\TeacherPortal;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\CourseSchedule; // <-- ¡AÑADIDO! Importar el modelo
use Illuminate\Support\Facades\Auth; // <-- ¡AÑADIDO! Para obtener el usuario autenticado
use Illuminate\Database\Eloquent\Collection; // <-- ¡AÑADIDO! Para inicializar la colección

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    // --- ¡AÑADIDO! ---
    // Definir la propiedad pública para que la vista la pueda usar
    public Collection $courseSchedules;

    /**
     * --- ¡AÑADIDO! ---
     * El método mount() se ejecuta una vez cuando el componente se carga.
     * Aquí es donde cargaremos los datos.
     */
    public function mount()
    {
        // Obtener el ID del profesor autenticado
        $teacherId = Auth::id();

        // Cargar los horarios de los cursos para este profesor
        // Eager-load (with) las relaciones que la vista necesita
        // withCount cuenta los estudiantes inscritos en cada sección
        $this->courseSchedules = CourseSchedule::where('teacher_id', $teacherId)
            ->with([
                'module.course', // Carga el módulo y el curso asociado
            ])
            ->withCount('enrollments') // Esto crea la propiedad 'enrollments_count'
            ->get();
    }

    /**
     * --- MODIFICADO ---
     * Ahora el método render solo necesita mostrar la vista.
     * La vista tendrá acceso automáticamente a la propiedad pública '$courseSchedules'.
     */
    public function render()
    {
        return view('livewire.teacher-portal.dashboard');
    }
}