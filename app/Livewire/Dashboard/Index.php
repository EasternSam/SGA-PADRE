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
use Spatie\Permission\Models\Role;
use Illuminate\Database\QueryException; // Importante para capturar errores de SQL

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
                        if (Role::where('name', 'teacher')->exists()) {
                            return User::role('teacher')->count();
                        } elseif (Role::where('name', 'Profesor')->exists()) {
                            return User::role('Profesor')->count();
                        }
                    } catch (\Exception $e) {
                        // Si falla la tabla roles, retornamos 0
                        return 0;
                    }
                    return 0;
                });
            } else {
                 $this->totalTeachers = 0; 
            }

            // Cargar actividades recientes optimizado - LIMITANDO CAMPOS PARA EVITAR MEMORY LEAK
            if (class_exists(ActivityLog::class)) {
                try {
                    $this->recentActivities = ActivityLog::with('causer:id,name') // Solo traer ID y nombre del usuario
                        ->latest()
                        ->take(5)
                        ->get(['id', 'description', 'causer_id', 'created_at']); // Solo campos necesarios
                } catch (QueryException $e) {
                    // Capturamos error si la tabla activity_log no existe
                    $this->addTrace('Advertencia: No se pudo cargar ActivityLog (posible falta de tabla): ' . $e->getMessage());
                    $this->recentActivities = new Collection(); // Fallback a vacío
                }
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
        // Clave única basada en la fecha actual (día) para refrescar diariamente o invalidar manualmente
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

            // En lugar de cargar modelos o plucks gigantes, usamos una consulta directa agregada
            // Contamos inscripciones creadas en el mes
            $totalEnrollmentsMonth = DB::table('enrollments')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            // Por defecto, asumimos que todos son del sistema si no podemos comprobar logs
            $systemCount = $totalEnrollmentsMonth;

            if ($totalEnrollmentsMonth > 0 && class_exists(ActivityLog::class)) {
                try {
                    // ESTRATEGIA ULTRARÁPIDA: 
                    // Contamos logs de creación de Enrollments en ese mes que tengan un usuario responsable (causer_id).
                    // Asumimos que si hay un log de creación con usuario en ese mes, corresponde a una inscripción "Sistema".
                    // Esto evita el JOIN costoso o el WHERE IN con miles de IDs.
                    
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
                    // Si la tabla activity_log no existe, capturamos el error silenciosamente 
                    // y mantenemos $systemCount = $totalEnrollmentsMonth (fallback seguro)
                    $this->addTrace("Fallo consulta activity_log mes {$month}: " . $e->getMessage());
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
        // Consulta base optimizada con Eager Loading selectivo para la TABLA
        // Solo traemos los campos necesarios para pintar la tabla
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
        // take(5) ya limita mucho, pero el select ayuda.
        $recentEnrollments = $query->take(5)->get();

        return view('livewire.dashboard.index', [
            'recentEnrollments' => $recentEnrollments,
            'chartLabels' => $this->chartLabels,
            'chartDataWeb' => $this->chartDataWeb,
            'chartDataSystem' => $this->chartDataSystem,
        ]);
    }
}