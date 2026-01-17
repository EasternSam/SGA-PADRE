<?php

namespace App\Livewire\Dashboard;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\WordpressApiService; // Importante: Importar el servicio
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
     * Inyectamos el servicio WordpressApiService
     */
    public function mount(WordpressApiService $wpService)
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

            // 3. Preparar datos para el Gráfico (Pasamos el servicio)
            $this->prepareChartData($wpService);

        } catch (\Exception $e) {
            \Log::error("Error cargando Dashboard Stats: " . $e->getMessage());
        }
    }

    /**
     * Prepara los datos para el gráfico de inscripciones.
     * Combina datos locales (Sistema) con datos remotos de WordPress (Web).
     */
    private function prepareChartData(WordpressApiService $wpService)
    {
        $this->chartLabels = [];
        $this->chartDataWeb = [];
        $this->chartDataSystem = [];

        // 1. Obtener datos remotos de WordPress (Web)
        // Esto devuelve arrays como ['labels' => ['Ene', 'Feb'...], 'data' => [10, 5...]]
        $wpStats = $wpService->getEnrollmentStats();
        $wpDataMap = []; // Mapa auxiliar para búsqueda rápida por mes

        if (!empty($wpStats['labels']) && !empty($wpStats['data'])) {
            // Creamos un mapa 'Mes' => Cantidad para alinear con nuestro bucle
            foreach ($wpStats['labels'] as $index => $label) {
                $wpDataMap[$label] = $wpStats['data'][$index] ?? 0;
            }
        }

        // 2. Calcular datos locales (Sistema) y construir arrays finales
        // Iteramos hacia atrás 6 meses hasta hoy (Total 7 meses)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            // Nombre del mes (Ej: Ene, Feb...)
            // Nota: Debemos asegurar que el formato coincida con el que devuelve WP (Ej: "Ene")
            // ucfirst es importante.
            $monthLabel = ucfirst($date->locale('es')->isoFormat('MMM'));
            
            // Agregamos la etiqueta al eje X
            $this->chartLabels[] = $monthLabel;

            // --- Datos WEB (Desde WP) ---
            // Buscamos si existe el dato en lo que trajo la API, sino 0
            $webCount = $wpDataMap[$monthLabel] ?? 0;
            $this->chartDataWeb[] = $webCount;

            // --- Datos SISTEMA (Local) ---
            // Contamos inscripciones creadas localmente en este mes
            // Usamos Enrollment::query() para consistencia
            $systemCount = 0;
            
            if (class_exists(ActivityLog::class)) {
                // Buscamos inscripciones creadas en este mes que tengan un causante (usuario logueado)
                // Primero obtenemos los IDs de inscripciones del mes
                $enrollmentIds = Enrollment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->pluck('id');

                if ($enrollmentIds->isNotEmpty()) {
                    $systemCount = ActivityLog::where('subject_type', Enrollment::class)
                        ->whereIn('subject_id', $enrollmentIds)
                        ->where('event', 'created')
                        ->whereNotNull('causer_id') // Creado por un usuario del sistema
                        ->distinct('subject_id')
                        ->count('subject_id');
                }
            } else {
                // Fallback simple si no hay logs: contar inscripciones del mes
                // (Asumiendo que si no hay logs, todo es local)
                $systemCount = Enrollment::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }

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