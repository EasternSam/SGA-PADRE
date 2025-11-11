<?php

namespace App\Livewire\Dashboard;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\student; // El modelo está en minúscula según tus archivos
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Collection; // Importar Collection

#[Layout('layouts.dashboard')] // <-- ¡CORRECCIÓN! El layout es 'layouts.dashboard'
class Index extends Component
{
    // --- MEJORA: Añadir propiedades para las estadísticas ---
    public $totalStudents = 0;
    public $totalCourses = 0;
    public $totalTeachers = 0;
    public $totalEnrollments = 0;
    
    // --- ¡¡¡CORRECCIÓN!!! ---
    // Inicializar la propiedad como una colección vacía.
    public Collection $recentEnrollments;

    /**
     * --- MEJORA: Cargar los datos cuando el componente se inicia ---
     * Carga las estadísticas clave una vez.
     */
    public function mount()
    {
        // Inicializar como colección vacía antes del try-catch
        $this->recentEnrollments = new Collection();
        
        try {
            $this->totalStudents = student::count();
            $this->totalCourses = Course::count();
            $this->totalEnrollments = Enrollment::count();
            
            // --- ¡¡¡CORRECCIÓN!!! ---
            // Asume que los profesores tienen el rol 'Profesor' (en español).
            $this->totalTeachers = User::role('Profesor')->count(); // Cambiado de 'teacher'

            // --- AÑADIDO: Cargar inscripciones recientes ---
            $this->recentEnrollments = Enrollment::with([
                'student', 
                'courseSchedule.module.course', 
                'courseSchedule.teacher'
            ])
            ->latest() // Ordena por created_at DESC
            ->take(5)  // Limita a 5 resultados
            ->get();

        } catch (\Exception $e) {
            // Manejar error si las tablas o roles no existen aún
            // Los valores se quedarán en 0, y $recentEnrollments será una colección vacía.
            \Log::error("Error al cargar datos del dashboard: " . $e->getMessage());
        }
    }

    public function render()
    {
        // La vista ya se está cargando, solo necesita las propiedades
        // que definimos en mount().
        return view('livewire.dashboard.index');
    }
}