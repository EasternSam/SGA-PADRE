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
     * Prepara los datos para el gráfico de inscripciones con datos reales.
     * Intenta distinguir entre Web (API) y Sistema basándose en ActivityLog.
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

            // --- 1. Obtener IDs de las inscripciones del mes ---
            // Optimizamos la consulta usando pluck directo
            $enrollmentsInMonthIds = Enrollment::query()
                ->where(function($q) use ($date) {
                    $q->whereYear('enrollment_date', $date->year)
                      ->whereMonth('enrollment_date', $date->month);
                })
                ->orWhere(function($q) use ($date) {
                    $q->whereNull('enrollment_date')
                      ->whereYear('created_at', $date->year)
                      ->whereMonth('created_at', $date->month);
                })
                ->pluck('id'); // Obtenemos colección de IDs directamente

            $totalCount = $enrollmentsInMonthIds->count();

            // --- 2. Clasificación API vs Sistema (Datos Reales) ---
            $systemCount = 0;
            $webCount = 0;

            if ($totalCount > 0) {
                // Si existe la clase de logs y hay inscripciones
                if (class_exists(ActivityLog::class)) {
                    // Contamos IDs ÚNICOS de inscripciones que tienen log de creación por un usuario
                    $systemCount = ActivityLog::where('subject_type', Enrollment::class)
                        ->whereIn('subject_id', $enrollmentsInMonthIds)
                        ->where('event', 'created') // Evento de creación
                        ->whereNotNull('causer_id') // Hecho por un usuario logueado
                        ->distinct('subject_id')    // Aseguramos no contar doble si hay logs duplicados
                        ->count('subject_id');
                    
                    // El resto se asume Web/API (sin usuario logueado al momento de crear)
                    $webCount = max(0, $totalCount - $systemCount);
                } else {
                    // Si no hay sistema de logs, todo se cuenta como Sistema por defecto para no confundir
                    $systemCount = $totalCount;
                }
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
            // Pasamos las propiedades públicas explícitamente
            'chartLabels' => $this->chartLabels,
            'chartDataWeb' => $this->chartDataWeb,
            'chartDataSystem' => $this->chartDataSystem,
        ]);
    }
}