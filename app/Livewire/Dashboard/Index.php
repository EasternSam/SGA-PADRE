<?php

namespace App\Livewire\Dashboard;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\WordpressApiService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema; // Importante para verificar tablas
use Spatie\Permission\Models\Role;
use Illuminate\Database\QueryException;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    // Estadísticas (Contadores Estáticos)
    public $totalStudents = 0;
    public $totalCourses = 0;
    public $totalTeachers = 0;
    public $totalEnrollments = 0;
    
    // Estado del Filtro de Inscripciones
    public $enrollmentFilter = 'all'; 

    // Colección para actividades
    public Collection $recentActivities;

    // Datos para el gráfico
    public $chartLabels = [];
    public $chartDataWeb = [];
    public $chartDataSystem = [];

    // VARIABLE DE DEBUG
    public $debugTrace = [];

    /**
     * Carga inicial de datos estáticos.
     */
    public function mount(WordpressApiService $wpService)
    {
        // Inicializar como colección vacía por defecto
        $this->recentActivities = new Collection();
        $this->addTrace('Inicio del componente Dashboard');

        try {
            // Optimizaciones de conteo: count() es ligero
            // Usamos Cache para evitar contar en cada recarga si no es necesario (TTL corto 10 mins)
            $this->totalStudents = Cache::remember('dashboard_total_students', 600, fn() => Student::count());
            $this->totalCourses = Cache::remember('dashboard_total_courses', 600, fn() => Course::count());
            $this->totalEnrollments = Cache::remember('dashboard_total_enrollments', 600, fn() => Enrollment::count());
            
            // Verificación de roles segura
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $this->totalTeachers = Cache::remember('dashboard_total_teachers', 600, function() {
                    try {
                        // Verificar si la tabla roles existe antes de consultar
                        if (Schema::hasTable('roles')) {
                            if (Role::where('name', 'teacher')->exists()) {
                                return User::role('teacher')->count();
                            } elseif (Role::where('name', 'Profesor')->exists()) {
                                return User::role('Profesor')->count();
                            }
                        }
                    } catch (\Exception $e) {
                        return 0;
                    }
                    return 0;
                });
            } else {
                 $this->totalTeachers = 0; 
            }

            // Cargar actividades recientes optimizado
            // VERIFICACIÓN CRÍTICA: Asegurar que la tabla existe antes de consultar
            if (class_exists(ActivityLog::class) && Schema::hasTable('activity_log')) {
                try {
                    $this->recentActivities = ActivityLog::with('causer:id,name') // Solo traer ID y nombre del usuario
                        ->latest()
                        ->take(5)
                        ->get(['id', 'description', 'causer_id', 'created_at']); // Solo campos necesarios
                } catch (QueryException $e) {
                    $this->addTrace('Advertencia: Error al cargar ActivityLog: ' . $e->getMessage());
                    $this->recentActivities = new Collection(); // Fallback a vacío
                }
            } else {
                $this->addTrace('Advertencia: Tabla activity_log no encontrada o clase no existe.');
            }

            // Preparar gráfico
            $this->prepareChartData($wpService);

        } catch (\Exception $e) {
            $this->addTrace('ERROR CRÍTICO EN MOUNT: ' . $e->getMessage());
            Log::error("Dashboard Error: " . $e->getMessage());
        }
    }

    /**
     * Helper para añadir trazas de debug
     */
    private function addTrace($message, $data = null)
    {
        // Limitamos el tamaño de la data logueada para ahorrar memoria
        $dataPreview = $data;
        if (is_array($data) && count($data) > 5) { // Límite más estricto
            $dataPreview = array_slice($data, 0, 5) + ['...' => 'truncated'];
        } elseif (is_string($data) && strlen($data) > 200) {
            $dataPreview = substr($data, 0, 200) . '... (truncated)';
        }

        $entry = Carbon::now()->toTimeString() . ' - ' . $message;
        $this->debugTrace[] = $entry;
        
        // Log reducido para producción
        // Log::info('[DASHBOARD_DEBUG] ' . $message); 
    }

    /**
     * Prepara los datos para el gráfico de inscripciones.
     */
    private function prepareChartData(WordpressApiService $wpService)
    {
        // Intentamos recuperar del caché primero (TTL 1 hora) para velocidad extrema
        $cacheKey = 'dashboard_chart_stats_' . Carbon::now()->format('Y-m-d_H'); 
        
        $cachedStats = Cache::remember($cacheKey, 3600, function () use ($wpService) {
            return $this->calculateChartStats($wpService);
        });

        $this->chartLabels = $cachedStats['labels'];
        $this->chartDataWeb = $cachedStats['web'];
        $this->chartDataSystem = $cachedStats['system'];
        
        $this->addTrace('Datos de gráfico cargados (Origen: ' . (Cache::has($cacheKey) ? 'Cache' : 'Calculado') . ')');
    }

    /**
     * Lógica pesada de cálculo de estadísticas (solo se ejecuta si no hay caché)
     */
    private function calculateChartStats(WordpressApiService $wpService) {
        $labels = [];
        $dataWeb = [];
        $dataSystem = [];

        $this->addTrace('Calculando estadísticas (sin caché)...');

        // 1. Obtener datos de WP API
        try {
            $wpStats = $wpService->getEnrollmentStats();
        } catch (\Exception $e) {
            $this->addTrace('Error WP API: ' . $e->getMessage());
            $wpStats = ['labels' => [], 'data' => []];
        }

        $wpDataMap = [];
        if (!empty($wpStats['labels']) && !empty($wpStats['data'])) {
            foreach ($wpStats['labels'] as $index => $label) {
                if (isset($wpStats['data'][$index])) {
                    $cleanLabel = $this->normalizeLabel($label);
                    $wpDataMap[$cleanLabel] = $wpStats['data'][$index];
                }
            }
        }

        // Verificar existencia de tablas una sola vez para el bucle
        $hasActivityLogTable = Schema::hasTable('activity_log');
        $hasEnrollmentsTable = Schema::hasTable('enrollments');

        // 2. Calcular datos locales
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            // Etiquetas
            $monthNameShort = $date->locale('es')->isoFormat('MMM'); 
            $monthLabel = ucfirst(str_replace('.', '', $monthNameShort)); 
            $labels[] = $monthLabel;

            // Datos Web (WP)
            $searchKey = $this->normalizeLabel($monthNameShort);
            $dataWeb[] = (int) ($wpDataMap[$searchKey] ?? 0);

            // Datos Sistema (Local) - CONSULTA OPTIMIZADA
            $year = $date->year;
            $month = $date->month;
            $systemCount = 0;

            if ($hasEnrollmentsTable) {
                // Contamos inscripciones creadas en el mes
                $totalEnrollmentsMonth = DB::table('enrollments')
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count();

                // Por defecto, asumimos que todos son del sistema si no podemos comprobar logs
                $systemCount = $totalEnrollmentsMonth;

                if ($totalEnrollmentsMonth > 0 && class_exists(ActivityLog::class) && $hasActivityLogTable) {
                    try {
                        // ESTRATEGIA ULTRARÁPIDA: 
                        $logSystemCount = DB::table('activity_log')
                            ->where('subject_type', Enrollment::class) // Asegúrate que el string coincida con lo guardado en BD
                            ->where('event', 'created')
                            ->whereNotNull('causer_id')
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->count();
                        
                        // Ajuste de seguridad: El conteo de logs no puede ser mayor al de inscripciones reales
                        $systemCount = min($logSystemCount, $totalEnrollmentsMonth);
                    } catch (QueryException $e) {
                        // Si falla la consulta, mantenemos el total como fallback
                        $this->addTrace("Fallo consulta activity_log mes {$month}: " . $e->getMessage());
                    }
                }
            }
            
            $dataSystem[] = (int) $systemCount;
        }

        return [
            'labels' => $labels,
            'web' => $dataWeb,
            'system' => $dataSystem
        ];
    }

    private function normalizeLabel($label)
    {
        return strtolower(trim(str_replace('.', '', $label)));
    }

    public function setFilter($status)
    {
        $this->enrollmentFilter = $status;
    }

    public function render()
    {
        // Verificar tabla antes de consultar para evitar crash en render
        if (!Schema::hasTable('enrollments')) {
             return view('livewire.dashboard.index', [
                'recentEnrollments' => [],
                'chartLabels' => $this->chartLabels,
                'chartDataWeb' => $this->chartDataWeb,
                'chartDataSystem' => $this->chartDataSystem,
            ]);
        }

        // Consulta base optimizada con Eager Loading selectivo para la TABLA
        $query = Enrollment::with([
            'student:id,name,last_name,email,user_id', 
            'student.user:id,name,email',
            'courseSchedule:id,module_id,teacher_id', 
            'courseSchedule.module:id,course_id,name', 
            'courseSchedule.module.course:id,name', 
            'courseSchedule.teacher:id,name' 
        ])->latest();

        if ($this->enrollmentFilter !== 'all') {
            $query->where('status', $this->enrollmentFilter);
        }

        // Limitamos los campos de la tabla principal también
        $recentEnrollments = $query->take(5)->get();

        return view('livewire.dashboard.index', [
            'recentEnrollments' => $recentEnrollments,
            'chartLabels' => $this->chartLabels,
            'chartDataWeb' => $this->chartDataWeb,
            'chartDataSystem' => $this->chartDataSystem,
        ]);
    }
}