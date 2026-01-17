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
            // Usamos 'enrollment_date' como principal, fallback a 'created_at' si es null
            // Capturamos tanto fecha de matrícula como fecha de creación para no perder datos
            $enrollmentsInMonth = Enrollment::where(function($q) use ($date) {
                $q->whereYear('enrollment_date', $date->year)
                  ->whereMonth('enrollment_date', $date->month);
            })->orWhere(function($q) use ($date) {
                $q->whereNull('enrollment_date') // Solo si no tiene fecha de matrícula
                  ->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month);
            })->get(['id']); // Solo necesitamos el ID para consultar los logs

            $totalCount = $enrollmentsInMonth->count();
            $enrollmentIds = $enrollmentsInMonth->pluck('id')->toArray();

            // --- 2. Clasificación API vs Sistema (Datos Reales) ---
            $systemCount = 0;
            $webCount = 0;

            if ($totalCount > 0) {
                // Lógica basada en ActivityLog:
                // Si la creación fue logueada con un 'causer_id' (Usuario), es del Sistema.
                // Si la creación no tiene 'causer_id' (o no hay log específico pero el registro existe), asumimos Web/API/Automático.
                
                if (class_exists(ActivityLog::class) && !empty($enrollmentIds)) {
                    // Contamos cuántas de estas inscripciones tienen un log de creación con autor
                    $logsWithCauser = ActivityLog::where('subject_type', Enrollment::class)
                        ->whereIn('subject_id', $enrollmentIds)
                        ->where('event', 'created')
                        ->whereNotNull('causer_id')
                        ->count();
                    
                    $systemCount = $logsWithCauser;
                    
                    // La diferencia se asume como origen externo/web (sin usuario logueado en el momento de creación)
                    $webCount = max(0, $totalCount - $systemCount);
                } else {
                    // Fallback: Si no hay sistema de logs activo, asignamos todo a Sistema para no mostrar datos erróneos
                    $systemCount = $totalCount;
                    $webCount = 0;
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