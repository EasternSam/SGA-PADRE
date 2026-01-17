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

    // Colección para actividades (no requiere filtro por ahora)
    public Collection $recentActivities;

    // Datos para el gráfico
    public $chartLabels = [];
    public $chartDataWeb = [];
    public $chartDataSystem = [];

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
            
            // Verificar si Spatie Permission está instalado y configurado antes de usar 'role' scope
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                 // Usamos el rol 'teacher' (minúsculas) si es el slug estándar, o 'Profesor' según tu DB
                 // Ajusta 'teacher' o 'Profesor' según tus seeders.
                 $this->totalTeachers = User::role('teacher')->count(); 
            } else {
                 $this->totalTeachers = 0; 
            }


            // 2. Cargar Actividad Reciente (Timeline)
            if (class_exists(ActivityLog::class)) {
                $this->recentActivities = ActivityLog::with('causer')
                    ->latest()
                    ->take(6)
                    ->get();
            }

            // 3. Preparar datos para el Gráfico (Últimos 7 meses)
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

        // Iteramos hacia atrás 6 meses hasta hoy
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            // Nombre del mes corto (Ene, Feb...)
            $monthLabel = ucfirst($date->locale('es')->isoFormat('MMM'));
            $this->chartLabels[] = $monthLabel;

            // Contar inscripciones reales de ese mes y año
            $totalCount = Enrollment::whereYear('created_at', $date->year) // Usamos created_at que es estándar
                ->whereMonth('created_at', $date->month)
                ->count();

            // --- SIMULACIÓN DE ORIGEN (API vs SISTEMA) ---
            // Como no existe columna 'source' en la DB actual, simulamos una distribución
            // Usamos una semilla basada en el mes para que los datos sean consistentes al recargar
            srand($date->year + $date->month); 
            
            // Asumimos aleatoriamente que entre el 20% y el 60% vienen de la Web (API)
            $webPercentage = rand(20, 60) / 100; 
            
            $webCount = (int) round($totalCount * $webPercentage);
            $systemCount = $totalCount - $webCount;

            // Si no hay datos (0), ponemos 0 para que el gráfico no se rompa
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
        // Construcción de la consulta de inscripciones
        $query = Enrollment::with([
            'student.user', // Asumiendo que Student tiene relación con User para el nombre
            'courseSchedule.module.course', 
            'courseSchedule.teacher'
        ])->latest();

        // Aplicar filtro si no es 'all'
        if ($this->enrollmentFilter !== 'all') {
            $query->where('status', $this->enrollmentFilter);
        }

        // Obtener resultados limitados (ej. 5 últimos del filtro seleccionado)
        $recentEnrollments = $query->take(5)->get();

        return view('livewire.dashboard.index', [
            'recentEnrollments' => $recentEnrollments,
            'chartLabels' => $this->chartLabels,
            'chartDataWeb' => $this->chartDataWeb,
            'chartDataSystem' => $this->chartDataSystem,
        ]);
    }
}