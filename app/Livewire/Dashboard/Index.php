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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    // Colección para actividades
    public Collection $recentActivities;

    // Datos para el gráfico (Arrays públicos para Livewire)
    public $chartLabels = [];
    public $chartDataWeb = [];
    public $chartDataSystem = [];

    /**
     * Carga inicial de datos estáticos.
     */
    public function mount()
    {
        // Inicializar colecciones para evitar errores de tipo si falla la carga
        $this->recentActivities = new Collection();

        try {
            // 1. Cargar Contadores
            $this->totalStudents = Student::count();
            $this->totalCourses = Course::count();
            $this->totalEnrollments = Enrollment::count();
            
            // Verificar si Spatie Permission está instalado para contar profesores
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                 // Ajusta 'teacher' o 'Profesor' según el nombre exacto en tu tabla roles
                 $this->totalTeachers = User::role('teacher')->count(); 
                 // Si devuelve 0, intenta con 'Profesor' por si acaso
                 if ($this->totalTeachers === 0) {
                     $this->totalTeachers = User::role('Profesor')->count();
                 }
            } else {
                 $this->totalTeachers = 0; 
            }

            // 2. Cargar Actividad Reciente
            if (class_exists(ActivityLog::class)) {
                $this->recentActivities = ActivityLog::with('causer')
                    ->latest()
                    ->take(5)
                    ->get();
            }

            // 3. Preparar datos para el Gráfico
            $this->prepareChartData();

        } catch (\Exception $e) {
            \Log::error("Error cargando Dashboard Stats: " . $e->getMessage());
        }
    }

    /**
     * Prepara los datos para el gráfico de inscripciones.
     */
    private function prepareChartData()
    {
        $this->chartLabels = [];
        $this->chartDataWeb = [];
        $this->chartDataSystem = [];

        // Iteramos hacia atrás 6 meses hasta hoy (Total 7 meses)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            // Nombre del mes (Ej: Ene, Feb...)
            $monthLabel = ucfirst($date->locale('es')->isoFormat('MMM'));
            $this->chartLabels[] = $monthLabel;

            // --- CONSULTA DE DATOS ---
            // Usamos 'enrollment_date' como en tu backend original.
            // Si enrollment_date es null en la BD, podrías probar cambiarlo a 'created_at'.
            $totalCount = Enrollment::whereYear('enrollment_date', $date->year)
                ->whereMonth('enrollment_date', $date->month)
                ->count();

            // Si el conteo con enrollment_date da 0, intentamos fallback a created_at 
            // (útil si migraste datos y enrollment_date quedó vacío)
            if ($totalCount === 0) {
                $totalCount = Enrollment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }

            // --- SIMULACIÓN DE ORIGEN (API vs SISTEMA) ---
            // Como no hay columna 'source', distribuimos aleatoriamente para visualización
            // Usamos semilla basada en mes/año para que no cambie al recargar
            srand($date->year + $date->month); 
            
            if ($totalCount > 0) {
                $webPercentage = rand(20, 60) / 100; 
                $webCount = (int) round($totalCount * $webPercentage);
                $systemCount = $totalCount - $webCount;
            } else {
                $webCount = 0;
                $systemCount = 0;
            }

            $this->chartDataWeb[] = $webCount;
            $this->chartDataSystem[] = $systemCount;
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
        // Consulta base
        $query = Enrollment::with([
            'student.user', 
            'courseSchedule.module.course', 
            'courseSchedule.teacher'
        ])->latest();

        // Aplicar filtro
        if ($this->enrollmentFilter !== 'all') {
            $query->where('status', $this->enrollmentFilter);
        }

        // Obtener resultados
        $recentEnrollments = $query->take(5)->get();

        return view('livewire.dashboard.index', [
            'recentEnrollments' => $recentEnrollments,
            // Pasamos las propiedades públicas explícitamente, aunque Livewire las expone automáticamente
            'chartLabels' => $this->chartLabels,
            'chartDataWeb' => $this->chartDataWeb,
            'chartDataSystem' => $this->chartDataSystem,
        ]);
    }
}